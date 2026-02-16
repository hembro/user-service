<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Users;
use App\Http\Controllers\Api\V1\Users\EmailChangeController;
use App\Http\Middleware\EnsureDeviceIsTrusted;
use Illuminate\Support\Facades\Route;

Route::post('/register', Users\RegisterController::class)
    ->middleware(['guest', 'throttle:auth.register'])
    ->name('register');

Route::middleware([
    'auth:api',
    'throttle:auth.api',
    EnsureDeviceIsTrusted::class,
])->group(function () {

    Route::get('/profile', Users\ShowProfileController::class)
        ->name('profile');

    Route::put('/profile', Users\UpdateProfileController::class)
        ->name('profile.update');

    Route::patch('/profile/password', Users\UpdatePasswordController::class)
        ->name('profile.password.update');

    Route::post('/profile/avatar', Users\UpdateAvatarController::class)
        ->name('profile.avatar.update');

    Route::post('/profile/email/request', [EmailChangeController::class, 'request'])
        ->name('email.change.request');

    Route::post('/profile/email/verify', [EmailChangeController::class, 'verify'])
        ->name('email.change.verify');
});
