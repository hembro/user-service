<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\UserLoggedOut;
use App\Models\User;

final readonly class LogoutUser
{
    public function __construct(
        private readonly DeviceTrustVerifier $deviceService
    ) {}

    public function handle(User $user, string $deviceId, RequestMetadata $metadata): void
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
            deviceId: $deviceId
        );

        UserLoggedOut::dispatch($user, $metadata);
    }
}
