<?php

namespace App\Models;

use App\Jobs\SendRegisterOtpEmail;
use App\Jobs\SendResetPasswordOtpEmail;
use App\Support\OtpRateLimiter;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
