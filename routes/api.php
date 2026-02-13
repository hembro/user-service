<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')
        ->name('api.v1.auth.')
        ->group(base_path('routes/api/v1/auth.php'));

    Route::prefix('users')
        ->name('api.v1.users.')
        ->group(base_path('routes/api/v1/users.php'));

    Route::prefix('admin/users')
        ->name('api.v1.admin.users.')
        ->group(base_path('routes/api/v1/Admin/users.php'));

    Route::prefix('admin/auth')
        ->name('api.v1.admin.auth.')
        ->group(base_path('routes/api/v1/Admin/auth.php'));

    Route::prefix('system')
        ->name('api.v1.system.')
        ->group(base_path('routes/api/v1/system.php'));
});
