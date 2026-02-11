<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
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
        $tokenDto = $this->service->login(
            credentials: LoginCredentials::fromRequest($request),
            metadata: RequestMetadata::fromRequest($request)
        );

        return $this->success(
            data: new TokenResource($tokenDto)
        )->withCookie(
            cookie: $this->cookie->make(
                refreshToken: $tokenDto->refreshToken
            )
        );
    }
}
