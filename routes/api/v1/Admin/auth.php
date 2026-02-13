<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Http\Controllers\Api\V1\Admin\Auth;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

Route::middleware([
    'auth:api',
    CheckTokenForAnyScope::using(Roles::adminRoles(returnString: true)),
])->group(function () {

    Route::post('/{user}/impersonate', Auth\ImpersonateController::class)
        ->can('impersonate,user')
        ->name('impersonate');
});
