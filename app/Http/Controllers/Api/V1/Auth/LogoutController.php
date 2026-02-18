<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\LogoutUser;
use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController
{
    use HasApiResponse;

    public function __construct(
        private readonly LogoutUser $action,
        private readonly DeviceTrustVerifier $deviceService,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $deviceId = $this->deviceService->resolveDeviceId($request);

        $this->action->handle(
            user: $request->user(),
            deviceId: $deviceId,
            metadata: RequestMetadata::fromRequest($request)
        );

        return $this->noContent()
            ->withCookie(
                $this->cookie->forgetRefreshToken()
            );
    }
}
