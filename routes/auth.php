<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\PhoneVerificationController;

// Routes d'authentification
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/send-otp', [LoginController::class, 'sendOtp'])->name('login.send-otp');
    Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('login.verify-otp');
    
    // Phone Login Routes
    Route::get('/login/phone', [LoginController::class, 'showPhoneLoginForm'])
        ->name('login.phone');
    Route::post('/login/phone/send-otp', [LoginController::class, 'sendOtp'])
        ->name('login.phone.send-otp');
    Route::post('/login/phone/verify-otp', [LoginController::class, 'loginWithOtp'])
        ->name('login.phone.verify-otp');

    // Registration routes
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/register-phone', [RegisterController::class, 'createWithPhone'])->name('register.phone');
    Route::post('/register-phone/send-otp', [RegisterController::class, 'sendRegistrationOtp'])->name('register.send-otp');
    Route::post('/register-phone/verify-otp', [RegisterController::class, 'verifyRegistrationOtp'])->name('register.verify-otp');
    
    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Email verification routes
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->middleware('throttle:6,1')
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Password confirmation routes
    Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store']);
    
    // Phone Verification Routes
    Route::get('/verify-phone', [PhoneVerificationController::class, 'show'])
        ->name('phone.verification.notice');
    Route::post('/verify-phone', [PhoneVerificationController::class, 'send'])
        ->name('phone.verification.send');
    Route::post('/verify-phone/verify', [PhoneVerificationController::class, 'verify'])
        ->name('phone.verification.verify');
});
