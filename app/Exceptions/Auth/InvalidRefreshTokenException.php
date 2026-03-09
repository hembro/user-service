<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Services\AuthCookieService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvalidRefreshTokenException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        $cookieService = app(AuthCookieService::class);

        return JsonResponse::error(
            message: $this->getMessage(),
            code: Response::HTTP_UNAUTHORIZED,
        )->withCookie(
            cookie: $cookieService->forgetRefreshToken()
        );
    }
}
