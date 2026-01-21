<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RefreshTokenController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;

Route::middleware('throttle')->group(function (): void {
    Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
        ->name('oauth.token');

    Route::post('/login', LoginController::class)
        ->name('login');

    Route::post('/refresh', RefreshTokenController::class)
        ->name('refresh');
});
