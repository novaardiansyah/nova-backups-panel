<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
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
Route::get('/terms', [AuthController::class, 'showTerms'])->name('terms');
Route::get('/privacy', [AuthController::class, 'showPrivacy'])->name('privacy');
