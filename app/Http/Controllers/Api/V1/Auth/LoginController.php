<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Services\AuthCookieService;
use App\Services\AuthService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthService $service,
        private readonly AuthCookieService $cookie,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $token = $this->service->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->success(
            data: [
                'token_type' => 'Bearer',
                'access_token' => $token->accessToken,
                'expires_in' => $token->expiresIn,
            ]
        )->withCookie(
            cookie: $this->cookie->make(
                refreshToken: $token->refreshToken
            )
        );
    }
}
