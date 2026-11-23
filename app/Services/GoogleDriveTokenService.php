<?php

namespace App\Services;

use App\Models\StorageAccount;
use Illuminate\Support\Facades\Http;

class GoogleDriveTokenService
{
  public function getValidAccessToken(StorageAccount $account): string
  {
    $account->loadMissing('credential');
    $credential = $account->credential;

    if ($credential === null) {
      throw new \RuntimeException("Storage account [{$account->uid}] has no credentials.");
    }

    if ($credential->token_expires_at !== null && $credential->token_expires_at->gt(now()->addMinutes(5))) {
      return (string) $credential->access_token;
    }

    if (empty($credential->refresh_token)) {
      return (string) $credential->access_token;
    }

    $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
      'client_id' => (string) config('services.google.client_id'),
      'client_secret' => (string) config('services.google.client_secret'),
      'refresh_token' => (string) $credential->refresh_token,
      'grant_type' => 'refresh_token',
    ]);

    if (! $response->successful()) {
      $account->update(['status' => 'expired']);
      throw new \RuntimeException('Failed to refresh Google Drive access token.');
    }

    /** @var array{access_token?: string, expires_in?: int} $data */
    $data = $response->json();
    $newToken = (string) ($data['access_token'] ?? '');

    if ($newToken === '') {
      throw new \RuntimeException('Google OAuth token refresh returned an empty access token.');
    }

    $expiresIn = (int) ($data['expires_in'] ?? 3600);

    $credential->forceFill([
      'access_token' => $newToken,
      'token_expires_at' => now()->addSeconds($expiresIn),
    ])->save();

    if ($account->status !== 'connected') {
      $account->update(['status' => 'connected']);
    }

    return $newToken;
  }
}
