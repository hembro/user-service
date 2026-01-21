<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->name('api.v1.auth.')->group(__DIR__ . '/api/v1/auth.php');
Route::prefix('v1/user')->name('api.v1.user.')->group(__DIR__ . '/api/v1/user.php');
