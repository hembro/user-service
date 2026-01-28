<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Services\AuthCookieService;
use App\Services\AuthService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class RefreshTokenController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthService $service,
        private readonly AuthCookieService $cookieFactory,
    ) {}

    public function __invoke(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token') ?? $request->validated('refresh_token', '');

        $token = $this->service->refresh(
            refreshToken: $refreshToken,
            system: Systems::find($request->header('X-Source-System', ''))->value,
        );

        return $this->success(
            data: [
                'token_type' => 'Bearer',
                'access_token' => $token->accessToken,
                'expires_in' => $token->expiresIn,
            ]
        )->withCookie(
            cookie: $this->cookieFactory->make(
                refreshToken: $token->refreshToken
            )
        );
    }
}
