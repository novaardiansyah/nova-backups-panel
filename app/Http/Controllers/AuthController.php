<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\OtpRateLimiter;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Login', [
            'status' => session('status'),
            'loginStep' => session('login_step'),
            'loginEmail' => session('login_email'),
            'hasPassword' => session('login_has_password', false),
        ]);
    }

    public function identifyLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user->hasPassword()) {
            return redirect()->route('login')->with([
                'login_step' => 'password',
                'login_email' => $user->email,
                'login_has_password' => true,
            ]);
        }

        $remaining = OtpRateLimiter::check('login-otp', $email);
        if ($remaining !== null) {
            return redirect()->route('login')->with([
                'login_step' => 'otp',
                'login_email' => $user->email,
                'login_has_password' => false,
                'status' => 'A login code was recently sent. Please check your email or wait ' . OtpRateLimiter::formatRemaining($remaining) . ' before requesting a new code.',
            ]);
        }

        $user->sendLoginOtpNotification();

        return redirect()->route('login')->with([
            'login_step' => 'otp',
            'login_email' => $user->email,
            'login_has_password' => false,
            'status' => 'A 6-digit login code has been sent to your email.',
        ]);
    }

    public function sendLoginOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $remaining = OtpRateLimiter::check('login-otp', $email);
        if ($remaining !== null) {
            return redirect()->route('login')
                ->with([
                    'login_step' => 'otp',
                    'login_email' => $user->email,
                    'login_has_password' => $user->hasPassword(),
                ])
                ->withErrors([
                    'otp' => 'Please wait ' . OtpRateLimiter::formatRemaining($remaining) . ' before requesting a new code.',
                ]);
        }

        Cache::forget('auth:otp:login:lock:' . $email);
        $user->sendLoginOtpNotification();

        return redirect()->route('login')->with([
            'login_step' => 'otp',
            'login_email' => $user->email,
            'login_has_password' => $user->hasPassword(),
            'status' => 'A 6-digit login code has been sent to your email.',
        ]);
    }

    public function loginWithOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired login code.',
            ]);
        }

        $inputOtp = trim($validated['otp']);
        $cachedOtp = Cache::get('auth:otp:login:' . $email);

        if (! is_string($cachedOtp) || $cachedOtp === '' || ! hash_equals($cachedOtp, $inputOtp)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired login code.',
            ]);
        }

        Cache::forget('auth:otp:login:' . $email);
        Cache::forget('auth:otp:login:lock:' . $email);
        OtpRateLimiter::clear('login-otp', $email);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        if ($user instanceof User && ! $user->hasVerifiedEmail()) {
            $emailKey = strtolower(trim($user->email));
            if (OtpRateLimiter::check('register-verification', $emailKey) === null) {
                $user->sendEmailVerificationNotification();
            }

            return redirect()->route('verification.notice')->with(
                'status',
                'Please verify your email address with the 6-digit code sent to your email.',
            );
        }

        return redirect()->intended('/');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Please accept the Terms of Service and Privacy Policy to continue.',
        ]);

        $email = strtolower(trim($validated['email']));
        $name = Str::before($email, '@');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => null,
        ]);

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with(
            'status',
            'A 6-digit verification code has been sent to your email.',
        );
    }

    public function showVerifyEmail(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User || $user->hasVerifiedEmail()) {
            return redirect('/');
        }

        return Inertia::render('VerifyEmail', [
            'email' => $user->email,
            'status' => session('status'),
        ]);
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User || $user->hasVerifiedEmail()) {
            return redirect('/');
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $inputOtp = trim($validated['otp']);
        $cachedOtp = Cache::get('auth:otp:register:' . $user->id);

        if (! is_string($cachedOtp) || $cachedOtp === '' || ! hash_equals($cachedOtp, $inputOtp)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired verification code.',
            ]);
        }

        $emailKey = strtolower(trim($user->email));
        Cache::forget('auth:otp:register:' . $user->id);
        Cache::forget('auth:otp:register:lock:' . $user->id);
        OtpRateLimiter::clear('register-verification', $emailKey);

        if (! is_string($user->name) || trim($user->name) === '') {
            $user->forceFill([
                'name' => Str::before($emailKey, '@'),
            ])->save();
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect('/')->with('status', 'Email verified successfully.');
    }

    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User || $user->hasVerifiedEmail()) {
            return redirect('/');
        }

        $emailKey = strtolower(trim($user->email));
        $remaining = OtpRateLimiter::check('register-verification', $emailKey);
        if ($remaining !== null) {
            throw ValidationException::withMessages([
                'otp' => 'Please wait ' . OtpRateLimiter::formatRemaining($remaining) . ' before requesting a new code.',
            ]);
        }

        Cache::forget('auth:otp:register:lock:' . $user->id);
        $user->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $remaining = OtpRateLimiter::check('password-reset', $email);
        if ($remaining !== null) {
            throw ValidationException::withMessages([
                'email' => 'Please wait ' . OtpRateLimiter::formatRemaining($remaining) . ' before requesting a new code.',
            ]);
        }

        $generatedToken = null;
        $status = Password::broker()->sendResetLink(
            ['email' => $email],
            function (CanResetPassword $user, string $token) use (&$generatedToken): void {
                $generatedToken = $token;
                $user->sendPasswordResetNotification($token);
            },
        );

        if ($status !== Password::RESET_LINK_SENT || $generatedToken === null) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $request->session()->put('password_reset', [
            'email' => $email,
            'token' => $generatedToken,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $cooldown = OtpRateLimiter::hit('password-reset', $email);

        return redirect()->route('password.reset')->with(
            'status',
            'Verification code sent to your email. You can request another code in ' . OtpRateLimiter::formatRemaining($cooldown) . '.',
        );
    }

    public function showResetPassword(Request $request): Response|RedirectResponse
    {
        $resetSession = $request->session()->get('password_reset');
        if (! is_array($resetSession) || ! $this->isValidResetSession($resetSession)) {
            $request->session()->forget('password_reset');

            return redirect()->route('password.request')->with(
                'status',
                'Invalid or expired password reset session. Please request a new code.',
            );
        }

        return Inertia::render('ResetPassword', [
            'email' => $resetSession['email'],
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $resetSession = $request->session()->get('password_reset');
        if (! is_array($resetSession) || ! $this->isValidResetSession($resetSession)) {
            $request->session()->forget('password_reset');

            return redirect()->route('password.request')->with(
                'status',
                'Invalid or expired password reset session. Please request a new code.',
            );
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim($resetSession['email']));
        $cachedData = Cache::get('auth:otp:reset:' . $email);
        $storedOtp = is_array($cachedData) ? ($cachedData['otp'] ?? null) : null;
        $inputOtp = trim($validated['otp']);

        if (! is_string($storedOtp) || $storedOtp === '' || ! hash_equals($storedOtp, $inputOtp)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP code.',
            ]);
        }

        $status = Password::broker()->reset(
            [
                'email' => $email,
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $resetSession['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'otp' => __($status),
            ]);
        }

        Cache::forget('auth:otp:reset:' . $email);
        OtpRateLimiter::clear('password-reset', $email);
        $request->session()->forget('password_reset');

        return redirect()->route('login')->with(
            'status',
            'Your password has been reset. Please sign in with your new password.',
        );
    }

    public function resendResetOtp(Request $request): RedirectResponse
    {
        $resetSession = $request->session()->get('password_reset');
        if (! is_array($resetSession) || ! $this->isValidResetSession($resetSession)) {
            $request->session()->forget('password_reset');

            return redirect()->route('password.request')->with(
                'status',
                'Password reset session expired or invalid. Please request a new code.',
            );
        }

        $email = strtolower(trim($resetSession['email']));
        $remaining = OtpRateLimiter::check('password-reset', $email);
        if ($remaining !== null) {
            throw ValidationException::withMessages([
                'otp' => 'Please wait ' . OtpRateLimiter::formatRemaining($remaining) . ' before requesting a new code.',
            ]);
        }

        $cooldown = OtpRateLimiter::hit('password-reset', $email);
        $broker = Password::broker();
        $user = $broker->getUser(['email' => $email]);

        if ($user instanceof CanResetPassword) {
            $token = $broker->createToken($user);
            $user->sendPasswordResetNotification($token);

            $request->session()->put('password_reset', [
                'email' => $email,
                'token' => $token,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ]);
        }

        return back()->with(
            'status',
            'A new verification code has been sent. Please wait ' . OtpRateLimiter::formatRemaining($cooldown) . '.',
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showTerms(): Response
    {
        return Inertia::render('Terms');
    }

    public function showPrivacy(): Response
    {
        return Inertia::render('Privacy');
    }

    private function isValidResetSession(array $resetSession): bool
    {
        $email = is_string($resetSession['email'] ?? null) ? strtolower(trim($resetSession['email'])) : '';
        $token = is_string($resetSession['token'] ?? null) ? $resetSession['token'] : '';
        $expiresAt = is_int($resetSession['expires_at'] ?? null) ? $resetSession['expires_at'] : 0;

        if ($email === '' || $token === '' || $expiresAt < now()->timestamp) {
            return false;
        }

        $broker = Password::broker();
        $user = $broker->getUser(['email' => $email]);

        if (! $user instanceof CanResetPassword) {
            return false;
        }

        return $broker->tokenExists($user, $token);
    }
}
