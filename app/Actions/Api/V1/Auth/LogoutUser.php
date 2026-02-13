<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Models\User;

final readonly class LogoutUser
{
    public function execute(User $user): void
    {
        /** @var \Laravel\Passport\Token|null $accessToken */
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();
    }
}
