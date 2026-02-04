<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')
    ->name('api.v1.auth.')
    ->group(base_path('routes/api/v1/auth.php'));

Route::prefix('v1/users')
    ->name('api.v1.users.')
    ->group(base_path('routes/api/v1/users.php'));
