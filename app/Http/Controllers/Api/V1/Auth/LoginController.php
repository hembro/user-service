<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AttemptLogin;
use App\Commands\Auth\LoginCommand;
use App\Enums\Auth\AuthResultStatus;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Services\Auth\DeviceTrustService;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class LoginController
{
    use HasApiResponse;

    public function __construct(
        private readonly AttemptLogin $action,
        private readonly AuthCookieService $cookie,
        private readonly DeviceTrustService $deviceService
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $deviceId = $this->deviceService->resolveDeviceId($request) ?? (string) Str::orderedUuid();

        $outcome = $this->action->handle(
            LoginCommand::fromRequest($request, $deviceId)
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
