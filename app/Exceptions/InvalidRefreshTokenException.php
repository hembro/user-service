<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvalidRefreshTokenException extends Exception
{
    use HasApiResponse;

    public function render(Request $request): JsonResponse
    {
        $cookieService = app(AuthCookieService::class);

        return $this->error(
            message: 'The refresh token is invalid or expired.',
            code: Response::HTTP_UNAUTHORIZED,
        )->withCookie(
            cookie: $cookieService->forget()
        );
    }
}
