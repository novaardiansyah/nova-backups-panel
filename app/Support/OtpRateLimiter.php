<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class OtpRateLimiter
{
    public static function check(string $action, string $identifier): ?int
    {
        $cooldownKey = 'auth:otp:cooldown:' . $action . ':' . hash('sha256', strtolower(trim($identifier)));
        $cooldownUntil = (int) Cache::get($cooldownKey, 0);
        $remainingSeconds = $cooldownUntil - time();

        return $remainingSeconds > 0 ? $remainingSeconds : null;
    }

    public static function hit(string $action, string $identifier): int
    {
        $hash = hash('sha256', strtolower(trim($identifier)));
        $attemptsKey = 'auth:otp:attempts:' . $action . ':' . $hash;
        $cooldownKey = 'auth:otp:cooldown:' . $action . ':' . $hash;

        $attempts = (int) Cache::get($attemptsKey, 0) + 1;

        $cooldownSeconds = $attempts === 1
            ? 120
            : (15 + (($attempts - 2) * 5)) * 60;

        Cache::put($attemptsKey, $attempts, now()->addSeconds(max(86400, $cooldownSeconds + 3600)));
        Cache::put($cooldownKey, time() + $cooldownSeconds, now()->addSeconds($cooldownSeconds));

        return $cooldownSeconds;
    }

    public static function formatRemaining(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds === 1 ? '' : 's');
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
    }

    public static function clear(string $action, string $identifier): void
    {
        $hash = hash('sha256', strtolower(trim($identifier)));
        Cache::forget('auth:otp:attempts:' . $action . ':' . $hash);
        Cache::forget('auth:otp:cooldown:' . $action . ':' . $hash);
    }
}
