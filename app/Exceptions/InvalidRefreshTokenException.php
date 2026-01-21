<?php

declare(strict_types=1);

namespace App\Exceptions;

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

        return response()->json([
            'success' => false,
            'code' => Response::HTTP_UNAUTHORIZED,
            'message' => 'The refresh token is invalid or expired.',
        ], Response::HTTP_UNAUTHORIZED)
            ->withCookie($cookieService->forget());
    }
}
