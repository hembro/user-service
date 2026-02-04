<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Http\Controllers\Api\V1\Users;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

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

    Route::middleware([
        CheckTokenForAnyScope::using([Roles::PMS_ADMIN->value, Roles::HERDIN_ADMIN->value, Roles::PHRR_ADMIN->value]),
        'can:viewAny,' . User::class,
    ])->group(function () {

        Route::get('/', Users\IndexController::class)
            ->name('index');

        Route::post('/', Users\StoreController::class)
            ->name('store');
    });

    Route::get('/{user}', Users\ShowController::class)
        ->middleware('can:view,user')
        ->name('show');

    Route::patch('/{user}', Users\UpdateController::class)
        ->middleware('can:update,user')
        ->name('update');

    Route::delete('/{user}', Users\DeleteController::class)
        ->middleware('can:delete,user')
        ->name('delete');

    Route::patch('/{user}/role', Users\UpdateRoleController::class)
        ->middleware('can:updateRole,user')
        ->name('role.update');

    Route::patch('/{user}/status', Users\UpdateStatusController::class)
        ->middleware('can:updateStatus,user')
        ->name('status.update');

    Route::post('/{user}/reset-password', Users\ResetPasswordController::class)
        ->middleware('can:resetPassword,user')
        ->name('password.reset');
});
