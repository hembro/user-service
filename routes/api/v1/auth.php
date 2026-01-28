<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;

Route::middleware('throttle')->group(function (): void {
    Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
        ->name('oauth.token');

    Route::post('/login', Auth\LoginController::class)
        ->name('login');

    Route::post('/refresh', Auth\RefreshTokenController::class)
        ->name('refresh');

    Route::post('/logout', Auth\LogoutController::class)
        ->middleware('auth:api')
        ->name('logout');

    Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
        ->middleware(['auth:api', 'throttle:6,1'])
        ->name('verification.resend');
});
