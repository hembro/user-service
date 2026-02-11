<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\System\LookupsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {

    Route::get('/lookups', LookupsController::class)
        ->name('lookups');
});
