<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientAreaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/identify', [AuthController::class, 'identifyLogin'])->middleware('guest')->name('login.identify');
Route::post('/login/otp/send', [AuthController::class, 'sendLoginOtp'])->middleware('guest')->name('login.otp.send');
Route::post('/login/otp', [AuthController::class, 'loginWithOtp'])->middleware('guest')->name('login.otp');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->middleware('auth')->name('verification.notice');
Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('auth')->name('verification.verify');
Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth')->name('verification.send');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('guest')->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest')->name('password.update');
Route::post('/reset-password/resend', [AuthController::class, 'resendResetOtp'])->middleware('guest')->name('password.resend');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/client', [ClientAreaController::class, 'index'])->name('client.area');
    Route::put('/client/profile', [ClientAreaController::class, 'updateProfile'])->name('client.profile.update');
    Route::put('/client/preferences', [ClientAreaController::class, 'updatePreferences'])->name('client.preferences.update');
    Route::post('/onboarding', [ClientAreaController::class, 'completeOnboarding'])->name('onboarding.store');
    Route::post('/onboarding/skip', [ClientAreaController::class, 'skipOnboarding'])->name('onboarding.skip');
});
Route::get('/terms', [AuthController::class, 'showTerms'])->name('terms');
Route::get('/terms-of-service', [AuthController::class, 'showTerms'])->name('terms.service');
Route::get('/privacy', [AuthController::class, 'showPrivacy'])->name('privacy');
Route::get('/privacy-policy', [AuthController::class, 'showPrivacy'])->name('privacy.policy');
