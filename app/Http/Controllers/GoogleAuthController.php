<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\OAuthState;
use App\Models\StorageAccount;
use App\Models\StorageCredential;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Throwable;

class GoogleAuthController extends Controller
{
  public function redirectForAuth(): RedirectResponse
  {
    $state = Str::random(48);

    OAuthState::create([
      'state' => $state,
      'purpose' => 'auth',
      'expires_at' => now()->addMinutes(10),
    ]);

    /** @var GoogleProvider $provider */
    $provider = Socialite::driver('google');

    $url = $provider
      ->stateless()
      ->redirectUrl($this->redirectUri())
      ->with(['state' => $state, 'prompt' => 'select_account'])
      ->redirect()
      ->getTargetUrl();

    return redirect()->away($url);
  }

  public function redirectForDrive(Request $request): RedirectResponse
  {
    $user = $request->user();
    if (! $user instanceof User) {
      return redirect()->route('login');
    }

    $state = Str::random(48);

    OAuthState::create([
      'state' => $state,
      'purpose' => 'google_drive',
      'user_id' => $user->id,
      'expires_at' => now()->addMinutes(10),
    ]);

    /** @var GoogleProvider $provider */
    $provider = Socialite::driver('google');

    $url = $provider
      ->stateless()
      ->redirectUrl($this->redirectUri())
      ->scopes([
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
      ])
      ->with([
        'state' => $state,
        'access_type' => 'offline',
        'prompt' => 'consent select_account',
        'include_granted_scopes' => 'true',
      ])
      ->redirect()
      ->getTargetUrl();

    return redirect()->away($url);
  }

  public function callback(Request $request): RedirectResponse
  {
    $error = (string) $request->query('error', '');
    $state = (string) $request->query('state', '');
    $code = (string) $request->query('code', '');

    $oauthState = $state !== '' ? OAuthState::where('state', $state)->first() : null;
    $purpose = $oauthState?->purpose ?? 'auth';

    if ($error !== '') {
      $oauthState?->delete();

      $message = $error === 'access_denied'
        ? 'Google authorization was cancelled.'
        : "Google authorization failed: {$error}";

      return $this->failureRedirect($purpose, $message);
    }

    if ($oauthState === null || $oauthState->expires_at->isPast() || $code === '') {
      $oauthState?->delete();

      return $this->failureRedirect($purpose, 'Invalid or expired Google authorization session. Please try again.');
    }

    $userId = $oauthState->user_id;
    $oauthState->delete();

    try {
      /** @var GoogleProvider $provider */
      $provider = Socialite::driver('google');

      /** @var \Laravel\Socialite\Two\User $googleUser */
      $googleUser = $provider->stateless()->redirectUrl($this->redirectUri())->user();

      $email = strtolower(trim((string) $googleUser->getEmail()));
      $googleId = (string) $googleUser->getId();
      $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: strstr($email, '@', true)));

      if ($email === '') {
        return $this->failureRedirect($purpose, 'No email address was returned by Google.');
      }

      if ($purpose === 'google_drive') {
        return $this->handleDriveCallback($request, $userId, $email, $name, $googleUser);
      }

      return $this->handleAuthCallback($request, $email, $googleId, $name);
    } catch (Throwable $e) {
      report($e);

      return $this->failureRedirect($purpose, 'Unable to complete Google authorization. Please try again.');
    }
  }

  private function handleAuthCallback(Request $request, string $email, string $googleId, string $name): RedirectResponse
  {
    $user = User::where('google_id', $googleId)->first()
      ?? User::where('email', $email)->first();

    $isNewUser = false;

    if ($user !== null) {
      if (empty($user->google_id)) {
        $user->google_id = $googleId;
      }
      if (empty($user->name) && $name !== '') {
        $user->name = $name;
      }
      if ($user->email_verified_at === null) {
        $user->email_verified_at = now();
      }
      $user->save();
    } else {
      $isNewUser = true;
      $user = new User([
        'name' => $name !== '' ? $name : $email,
        'email' => $email,
        'google_id' => $googleId,
        'password' => null,
      ]);
      $user->email_verified_at = now();
      $user->save();
    }

    $user->preference()->firstOrCreate(
      ['user_id' => $user->id],
      ['theme' => 'system', 'locale' => 'en'],
    );

    Auth::login($user, true);
    $request->session()->regenerate();

    if ($isNewUser || ! $user->hasPassword()) {
      $request->session()->put('show_onboarding', true);
    }

    return redirect()->intended(route('client.area'));
  }

  private function handleDriveCallback(
    Request $request,
    ?int $userId,
    string $email,
    string $name,
    \Laravel\Socialite\Two\User $googleUser,
  ): RedirectResponse {
    $user = $userId !== null ? User::find($userId) : $request->user();
    if (! $user instanceof User) {
      return redirect()->route('login')->withErrors(['email' => 'Please sign in before connecting Google Drive.']);
    }

    if (! Auth::check()) {
      Auth::login($user, true);
    }

    $account = StorageAccount::firstOrNew([
      'provider' => 'google_drive',
      'account_email' => $email,
    ]);

    $account->name = $account->exists && $account->name !== '' ? $account->name : ($name !== '' ? "Google Drive ({$name})" : $email);
    $account->status = 'connected';
    $account->save();

    $existingRefreshToken = $account->credential?->refresh_token;
    $refreshToken = ! empty($googleUser->refreshToken) ? $googleUser->refreshToken : $existingRefreshToken;

    StorageCredential::updateOrCreate(
      ['storage_account_id' => $account->id],
      [
        'access_token' => (string) $googleUser->token,
        'refresh_token' => $refreshToken,
        'token_expires_at' => now()->addSeconds((int) ($googleUser->expiresIn ?: 3600)),
      ],
    );

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'storage_account.connected',
      'subject_type' => 'StorageAccount',
      'subject_id' => $account->id,
      'ip_address' => $request->ip(),
      'metadata' => ['provider' => 'google_drive', 'account_email' => $email],
    ]);

    return redirect('/client?tab=storages')->with('status', "Google Drive ({$email}) connected successfully.");
  }

  private function failureRedirect(string $purpose, string $message): RedirectResponse
  {
    if ($purpose === 'google_drive') {
      return redirect('/client?tab=storages')->with('status', $message);
    }

    return redirect()->route('login')->withErrors(['email' => $message]);
  }

  private function redirectUri(): string
  {
    return (string) config('services.google.redirect');
  }
}
