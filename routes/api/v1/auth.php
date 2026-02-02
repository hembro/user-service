<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// 1. High-Risk Login Routes (Brute Force Protection)
Route::post('/login', Auth\LoginController::class)
    ->middleware('throttle:auth.login')
    ->name('login');

// 2. Standard Auth Utilities
Route::middleware('throttle:auth.api')->group(function () {
    Route::post('/refresh', Auth\RefreshTokenController::class)->name('refresh');
    Route::post('/logout', Auth\LogoutController::class)
        ->middleware('auth:api')
        ->name('logout');

    // Verification link clicking doesn't need strict limits (it's signed)
    Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

// 3. Email Trigger Routes (Spam Protection)
Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
    ->middleware(['auth:api', 'throttle:auth.email'])
    ->name('verification.resend');
