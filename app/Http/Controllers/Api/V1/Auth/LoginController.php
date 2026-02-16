<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\AttemptLogin;
use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\AuthResultStatus;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController
{
    use HasApiResponse;

    public function __construct(
        private readonly AttemptLogin $action,
        private readonly AuthCookieService $cookie,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $outcome = $this->action->handle(
            credentials: LoginCredentials::fromRequest($request),
            metadata: RequestMetadata::fromRequest($request)
        );

        $response = match ($outcome->status) {
            AuthResultStatus::AUTHENTICATED => $this->success(
                data: new TokenResource($outcome->token)
            ),
            AuthResultStatus::REQUIRES_CHALLENGE => $this->success(
                data: new AuthResource($outcome),
                message: $outcome->challengeType->message()
            ),
        };

        $response->withCookie(
            $this->cookie->makeDeviceIdCookie($outcome->deviceId)
        );

        if ($outcome->status === AuthResultStatus::AUTHENTICATED) {
            $response->withCookie(
                $this->cookie->makeRefreshTokenCookie($outcome->token->refreshToken)
            );
        }

        return $response;
    }
}
