<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ProcessSocialLogin;
use App\DTOs\Api\V1\Auth\SocialLoginDTO;
use App\Enums\SocialProviders;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Services\Auth\DeviceTrustService;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class SocialAuthController
{
    use HasApiResponse;

    public function __construct(
        private readonly ProcessSocialLogin $action,
        private readonly AuthCookieService $cookieService,
        private readonly DeviceTrustService $deviceService
    ) {}

    public function __invoke(SocialLoginRequest $request, SocialProviders $provider): JsonResponse
    {
        $authentocationOutcome = $this->action->handle(
            dto: SocialLoginDTO::fromRequest($request),
            deviceId: $this->deviceService->resolveDeviceId($request) ?? (string) Str::orderedUuid()
        );

        return $this->success(
            data: new AuthResource($authentocationOutcome),
            message: 'Social authentication successful.'
        )
            ->withCookie($this->cookieService->makeRefreshTokenCookie($authentocationOutcome->token->refreshToken))
            ->withCookie($this->cookieService->makeDeviceIdCookie($authentocationOutcome->deviceId));
    }
}
