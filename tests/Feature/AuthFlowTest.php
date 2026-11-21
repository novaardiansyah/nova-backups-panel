<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_email_only_and_verify_otp(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'email' => 'nova@gmail.com',
            'agree' => true,
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'nova@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('nova', $user->name);
        $this->assertNull($user->password);
        $this->assertNull($user->email_verified_at);

        Mail::assertSent(RegisterOtpMail::class, function (RegisterOtpMail $mail) {
            return $mail->hasTo('nova@gmail.com');
        });

        $otp = Cache::get('auth:otp:register:' . $user->id);
        $this->assertIsString($otp);

        $verifyResponse = $this->actingAs($user)->post('/verify-email', [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect('/');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_identify_login_shows_password_step_when_user_has_password(): void
    {
        $user = User::create([
            'name' => 'nova',
            'email' => 'nova@example.com',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login/identify', [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('login_step', 'password');
        $response->assertSessionHas('login_email', $user->email);
        $response->assertSessionHas('login_has_password', true);
    }

    public function test_identify_login_sends_otp_when_user_has_no_password(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'nova',
            'email' => 'nopass@example.com',
            'password' => null,
        ]);

        $response = $this->post('/login/identify', [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('login_step', 'otp');
        $response->assertSessionHas('login_email', $user->email);
        $response->assertSessionHas('login_has_password', false);

        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $otp = Cache::get('auth:otp:login:' . $user->email);
        $this->assertIsString($otp);

        $loginResponse = $this->post('/login/otp', [
            'email' => $user->email,
            'otp' => $otp,
            'remember' => true,
        ]);

        $loginResponse->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_with_password_can_request_and_login_with_email_otp(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'nova',
            'email' => 'both@example.com',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);

        $sendResponse = $this->post('/login/otp/send', [
            'email' => $user->email,
        ]);

        $sendResponse->assertRedirect(route('login'));
        $sendResponse->assertSessionHas('login_step', 'otp');
        $sendResponse->assertSessionHas('login_has_password', true);

        $otp = Cache::get('auth:otp:login:' . $user->email);
        $this->assertIsString($otp);

        $invalidResponse = $this->post('/login/otp', [
            'email' => $user->email,
            'otp' => '000000',
        ]);
        $invalidResponse->assertSessionHasErrors('otp');
        $this->assertGuest();

        $validResponse = $this->post('/login/otp', [
            'email' => $user->email,
            'otp' => $otp,
        ]);
        $validResponse->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }
}
