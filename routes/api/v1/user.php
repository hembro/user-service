<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Users\MeController;
use App\Http\Controllers\Api\V1\Users\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/me', MeController::class)->middleware('auth:api')->name('me');
Route::post('/register', RegisterController::class)->middleware(['guest', 'throttle'])->name('register');
