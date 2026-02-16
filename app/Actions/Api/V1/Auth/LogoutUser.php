<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\UserLoggedOut;
use App\Models\User;
use App\Services\Auth\DeviceTrustService;
use Illuminate\Http\Request;

final readonly class LogoutUser
{
    public function __construct(
        private readonly DeviceTrustService $deviceService
    ) {}

    public function handle(User $user, Request $request, RequestMetadata $metadata): void
    {
        /** @var \Laravel\Passport\Token|null $accessToken */
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();

        $this->deviceService->forgetDevice(
            user: $user,
            deviceId: $this->deviceService->resolveDeviceId($request)
        );

        UserLoggedOut::dispatch($user, $metadata);
    }
}
