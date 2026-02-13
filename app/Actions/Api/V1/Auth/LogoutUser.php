<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\UserLoggedOut;
use App\Models\User;

final readonly class LogoutUser
{
    public function handle(User $user, RequestMetadata $metadata): void
    {
        /** @var \Laravel\Passport\Token|null $accessToken */
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();

        UserLoggedOut::dispatch($user, $metadata);
    }
}
