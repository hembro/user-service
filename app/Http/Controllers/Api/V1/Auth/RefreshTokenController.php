<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;

final class RefreshTokenController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthCookieService $cookieFactory,
    ) {}

    public function __invoke(RefreshTokenRequest $request)
    {
        //
    }
}
