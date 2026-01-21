<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthCookieService $cookieFactory
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        $request->user()->token()->refreshToken?->revoke();

        return $this->noContent()
            ->withCookie(
                cookie: $this->cookieFactory->forget()
            );
    }
}
