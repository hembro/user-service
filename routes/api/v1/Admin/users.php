<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Http\Controllers\Api\V1\Admin\Users;
use App\Models\User;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

Route::middleware([
    'auth:api',
    CheckTokenForAnyScope::using(Roles::adminRoles(returnString: true)),
])->group(function () {

    Route::middleware('can:viewAny,' . User::class)->group(function () {
        Route::get('/', Users\IndexController::class)->name('index');
        Route::post('/', Users\StoreController::class)->name('store');
    });

    Route::get('/{user}', Users\ShowController::class)
        ->can('view,user')
        ->name('show');

    Route::put('/{user}', Users\UpdateController::class)
        ->can('update,user')
        ->name('update');

    Route::delete('/{user}', Users\DeleteController::class)
        ->can('delete,user')
        ->name('delete');

    Route::patch('/{user}/role', Users\UpdateRoleController::class)
        ->can('updateRole,user')
        ->name('role.update');

    Route::patch('/{user}/status', Users\UpdateStatusController::class)
        ->can('updateStatus,user')
        ->name('status.update');

    Route::post('/{user}/reset-password', Users\ResetPasswordController::class)
        ->can('resetPassword,user')
        ->name('password.reset');

    Route::patch('/{user}/restore', Users\RestoreController::class)
        ->withTrashed()
        ->can('restore,user')
        ->name('restore');

    Route::post('/{user}/impersonate', Users\ImpersonateController::class)
        ->can('impersonate,user')
        ->name('impersonate');
});
