<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\RefreshTokenDTO;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
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
        $token = $this->service->refresh(
            dto: RefreshTokenDTO::fromRequest($request)
        );

        return $this->success(
            data: new TokenResource($token)
        )->withCookie(
            cookie: $this->cookieFactory->make(
                refreshToken: $token->refreshToken
            )
        );
    }
}
