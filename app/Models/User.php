<?php

namespace App\Models;

use App\Jobs\SendLoginOtpEmail;
use App\Jobs\SendRegisterOtpEmail;
use App\Jobs\SendResetPasswordOtpEmail;
use App\Models\Concerns\HasUid;
use App\Support\OtpRateLimiter;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

#[Fillable(['uid', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasUid, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function preferredTheme(): string
    {
        $theme = $this->preference?->theme;

        return in_array($theme, ['light', 'dark', 'system'], true) ? $theme : 'system';
    }

    public function preferredLocale(): string
    {
        $locale = $this->preference?->locale;

        return in_array($locale, ['en', 'id'], true) ? $locale : 'en';
    }

    public function hasPassword(): bool
    {
        return is_string($this->password) && $this->password !== '';
    }

    public function sendLoginOtpNotification(): void
    {
        $emailKey = strtolower(trim($this->email));
        $lockKey = 'auth:otp:login:lock:' . $emailKey;
        if (! Cache::add($lockKey, true, 5)) {
            return;
        }

        $otp = sprintf('%06d', random_int(100000, 999999));

        Cache::put('auth:otp:login:' . $emailKey, $otp, now()->addMinutes(10));
        OtpRateLimiter::hit('login-otp', $emailKey);

        SendLoginOtpEmail::dispatch($otp, $this->name, $this->email, '10 minutes');
    }

    public function sendEmailVerificationNotification(): void
    {
        $lockKey = 'auth:otp:register:lock:' . $this->id;
        if (! Cache::add($lockKey, true, 5)) {
            return;
        }

        $otp = sprintf('%06d', random_int(100000, 999999));
        $emailKey = strtolower(trim($this->email));

        Cache::put('auth:otp:register:' . $this->id, $otp, now()->addMinutes(10));
        OtpRateLimiter::hit('register-verification', $emailKey);

        SendRegisterOtpEmail::dispatch($otp, $this->name, $this->email, '10 minutes');
    }

    public function sendPasswordResetNotification($token): void
    {
        $otp = sprintf('%06d', random_int(100000, 999999));
        $emailKey = strtolower(trim($this->email));

        Cache::put('auth:otp:reset:' . $emailKey, [
            'otp' => $otp,
            'token' => (string) $token,
        ], now()->addMinutes(10));

        SendResetPasswordOtpEmail::dispatch($otp, $this->name, $this->email, '10 minutes');
    }
}
