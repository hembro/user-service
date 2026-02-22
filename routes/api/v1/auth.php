<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Middleware\EnsureDeviceIsTrusted;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'throttle:auth.login'])->group(function () {

    Route::post('/login', Auth\LoginController::class)
        ->name('login');

    Route::post('login/challenge', Auth\VerifyAuthenticationChallengeController::class)
        ->name('login.challenge');
});

// Standard Auth Utilities
Route::middleware('throttle:auth.api')->group(function () {

    Route::post('/refresh', Auth\RefreshTokenController::class)->name('refresh');

    Route::post('/verify/{id}/{hash}', [Auth\VerifyEmailController::class, 'verify'])
        ->middleware(['guest', 'signed'])
        ->name('verification.verify');

    Route::middleware(['auth:api', EnsureDeviceIsTrusted::class])->group(function () {

        // API GATEWAY VALIDATION ENDPOINT
        Route::get('/validate', fn () => response()->noContent()->withHeaders(['X-User-Id' => auth('api')->id()]))->name('validate');

        Route::post('/logout', Auth\LogoutController::class)
            ->name('logout');

        Route::post('/2fa/enable', Auth\EnableTwoFactorController::class)
            ->name('2fa.enable');

        Route::post('/2fa/confirm', Auth\ConfirmTwoFactorController::class)
            ->name('2fa.confirm');

        Route::post('/2fa/disable', Auth\DisableTwoFactorController::class)
            ->name('2fa.disable');

        Route::post('/2fa/recovery-codes', Auth\RegenerateRecoveryCodeController::class)
            ->name('2fa.recovery-codes');
    });
});

Route::middleware(['guest', 'throttle:auth.email'])->group(function () {

    Route::post('/forgot-password', Auth\ForgotPasswordController::class)
        ->name('password.email');

    Route::post('/reset-password', Auth\ResetPasswordController::class)
        ->name('password.update');

    Route::post('/verify/resend', [Auth\VerifyEmailController::class, 'resend'])
        ->name('verification.resend');
});

Route::middleware(['throttle:auth.login'])->group(function () {

    Route::get('/social/{provider}/redirect', Auth\SocialRedirectController::class)
        ->name('social.redirect');

    Route::post('social/{provider}/callback', Auth\SocialAuthController::class)
        ->name('social.callback');
});
