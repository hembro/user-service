<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Services\AuthCookieService;
use App\Services\AuthService;
use App\Traits\HasApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RefreshTokenController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthService $service,
        private readonly AuthCookieService $cookieFactory,
    ) {}

    public function __invoke(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token') ?? $request->validated('refresh_token');

        try {
            $tokenData = $this->service->proxyRefreshTokenGrant(
                refreshToken: $refreshToken
            );
        } catch (AuthenticationException $e) {
            return $this->error(
                message: $e->getMessage(),
                code: Response::HTTP_UNAUTHORIZED
            )->withCookie(
                cookie: $this->cookieFactory->forget()
            );
        }

        return $this->success(
            data: [
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'],
                'token_type' => 'Bearer',
            ]
        )->withCookie(
            cookie: $this->cookieFactory->make(
                refreshToken: $tokenData['refresh_token']
            )
        );
    }
}
