<?php

namespace Tests\Unit;

use App\Support\OtpRateLimiter;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OtpRateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_progressive_cooldown_and_reset_after_verification(): void
    {
        $email = 'nova@example.com';

        $this->assertNull(OtpRateLimiter::check('login-otp', $email));

        $firstCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(120, $firstCooldown);
        $this->assertNotNull(OtpRateLimiter::check('login-otp', $email));

        $secondCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(300, $secondCooldown);

        $thirdCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(600, $thirdCooldown);

        $fourthCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(900, $fourthCooldown);

        $fifthCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(1200, $fifthCooldown);

        OtpRateLimiter::clear('login-otp', $email);
        $this->assertNull(OtpRateLimiter::check('login-otp', $email));

        $resetCooldown = OtpRateLimiter::hit('login-otp', $email);
        $this->assertSame(120, $resetCooldown);
    }
}
