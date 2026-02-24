<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ProcessSocialLogin;
use App\Commands\Auth\SocialLoginCommand;
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
        $deviceId = $this->deviceService->resolveDeviceId($request) ?? (string) Str::ulid();

        $authenticationOutcome = $this->action->handle(
            SocialLoginCommand::fromRequest($request, $deviceId, $provider)
        );

        return $this->success(
            data: new AuthResource($authenticationOutcome),
            message: 'Social authentication successful.'
        )
            ->withCookie($this->cookieService->makeRefreshTokenCookie($authenticationOutcome->token->refreshToken))
            ->withCookie($this->cookieService->makeDeviceIdCookie($authenticationOutcome->deviceId));
    }
}
