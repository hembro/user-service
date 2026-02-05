<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Users;
use Illuminate\Support\Facades\Route;

Route::post('/register', Users\RegisterController::class)
    ->middleware(['guest', 'throttle:auth.register'])
    ->name('register');

Route::middleware('auth:api')->group(function () {

    Route::get('/me', Users\MeController::class)
        ->name('me');

    Route::put('/me', Users\UpdateMyProfileController::class)
        ->name('me.update');

    Route::patch('/me/password', Users\UpdateMyPasswordController::class)
        ->name('me.password.update');
});
