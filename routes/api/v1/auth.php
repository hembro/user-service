<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// High-Risk Login Routes (Brute Force Protection)
Route::post('/login', Auth\LoginController::class)
    ->middleware(['guest', 'throttle:auth.login'])
    ->name('login');

Route::post('login/challenge', Auth\VerifyAuthenticationChallengeController::class)
    ->middleware(['guest', 'throttle:auth.login'])
    ->name('login.challenge');

// Standard Auth Utilities
Route::middleware('throttle:auth.api')->group(function () {

    Route::post('/refresh', Auth\RefreshTokenController::class)->name('refresh');

    Route::post('/logout', Auth\LogoutController::class)
        ->middleware('auth:api')
        ->name('logout');

    // Verification link clicking doesn't need strict limits (it's signed)
    Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/2fa/enable', Auth\EnableTwoFactorController::class)
        ->middleware('auth:api')
        ->name('2fa.enable');

    Route::post('/2fa/confirm', Auth\ConfirmTwoFactorController::class)
        ->middleware('auth:api')
        ->name('2fa.confirm');
});

// Email Trigger Routes (Spam Protection)
Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
    ->middleware(['auth:api', 'throttle:auth.email'])
    ->name('verification.resend');

Route::middleware(['guest', 'throttle:auth.email'])->group(function () {

    Route::post('/forgot-password', Auth\ForgotPasswordController::class)
        ->name('password.email');

    Route::post('/reset-password', Auth\ResetPasswordController::class)
        ->name('password.update');
});

Route::middleware(['guest', 'throttle:auth.login'])->group(function () {

    Route::get('/social/{provider}/redirect', Auth\SocialRedirectController::class)
        ->name('social.redirect');

    Route::post('social/{provider}/callback', Auth\SocialAuthController::class)
        ->name('social.callback');
});
