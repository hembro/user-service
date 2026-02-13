<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;

final class SocialAuthController
{
    use HasApiResponse;

    public function __construct(
        private AuthCookieService $cookie
    ) {}
}
