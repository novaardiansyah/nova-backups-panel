<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupProfileController;
use App\Http\Controllers\BackupRunController;
use App\Http\Controllers\ClientAreaController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\StorageAccountController;
use App\Models\Faq;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'isAuthenticated' => auth()->check(),
    'userLocale' => auth()->user()?->preferredLocale() ?? 'en',
    'faqs' => Schema::hasTable('faqs')
        ? Faq::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Faq $faq) => [
                'uid' => $faq->uid,
                'question' => [
                    'en' => $faq->question_en,
                    'id' => $faq->question_id,
                ],
                'answer' => [
                    'en' => $faq->answer_en,
                    'id' => $faq->answer_id,
                ],
            ])
        : [],
]))->name('home');
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
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectForAuth'])->name('auth.google.redirect');
Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/client', [ClientAreaController::class, 'index'])->name('client.area');
    Route::put('/client/profile', [ClientAreaController::class, 'updateProfile'])->name('client.profile.update');
    Route::put('/client/preferences', [ClientAreaController::class, 'updatePreferences'])->name('client.preferences.update');
    Route::get('/client/storage-accounts/google/redirect', [GoogleAuthController::class, 'redirectForDrive'])->name('client.storage-accounts.google.redirect');
    Route::put('/client/storage-accounts/{storageAccount:uid}', [StorageAccountController::class, 'update'])->name('client.storage-accounts.update');
    Route::delete('/client/storage-accounts/{storageAccount:uid}', [StorageAccountController::class, 'destroy'])->name('client.storage-accounts.destroy');
    Route::post('/client/agents', [AgentController::class, 'store'])->name('client.agents.store');
    Route::put('/client/agents/{agent:uid}', [AgentController::class, 'update'])->name('client.agents.update');
    Route::post('/client/agents/{agent:uid}/rotate-token', [AgentController::class, 'rotateToken'])->name('client.agents.rotate-token');
    Route::delete('/client/agents/{agent:uid}', [AgentController::class, 'destroy'])->name('client.agents.destroy');
    Route::post('/client/backup-profiles', [BackupProfileController::class, 'store'])->name('client.backup-profiles.store');
    Route::put('/client/backup-profiles/{backupProfile:uid}', [BackupProfileController::class, 'update'])->name('client.backup-profiles.update');
    Route::delete('/client/backup-profiles/{backupProfile:uid}', [BackupProfileController::class, 'destroy'])->name('client.backup-profiles.destroy');
    Route::post('/client/backup-profiles/{backupProfile:uid}/run', [BackupRunController::class, 'store'])->name('client.backup-profiles.run');
    Route::post('/client/backup-runs/{backupRun:uid}/retry', [BackupRunController::class, 'retry'])->name('client.backup-runs.retry');
    Route::post('/onboarding', [ClientAreaController::class, 'completeOnboarding'])->name('onboarding.store');
    Route::post('/onboarding/skip', [ClientAreaController::class, 'skipOnboarding'])->name('onboarding.skip');
});
Route::get('/terms-of-service', [AuthController::class, 'showTerms'])->name('terms.service');
Route::get('/privacy-policy', [AuthController::class, 'showPrivacy'])->name('privacy.policy');
