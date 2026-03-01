<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RefreshUserToken;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Services\Auth\DeviceTrustService;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;

final class RefreshTokenController
{
    use HasApiResponse;

    public function __construct(
        private readonly RefreshUserToken $action,
        private readonly DeviceTrustService $deviceService,
        private readonly AuthCookieService $cookie,
    ) {}

    public function __invoke(RefreshTokenRequest $request)
    {
        $deviceId = $this->deviceService->resolveDeviceId($request);

        $token = $this->action->handle(
            refreshToken: $request->cookie(config('cookie.refresh_token.name')) ?? $request->validated('refresh_token'),
            deviceId: $deviceId,
            system: $request->attributes->get('system')
        );

        return $this->success(
            data: new TokenResource($token)
        )->withCookie(
            $this->cookie->makeRefreshTokenCookie($token->refreshToken)
        );
    }
}
