<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\TokenIssuer;

final readonly class AuthenticateSocialUser
{
    public function __construct(
        private TokenIssuer $issuer
    ) {}

    public function handle(User $user, Systems $system): TokenDTO
    {
        if ($user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException('Account is inactive.');
        }

        return $this->issuer->issueFullToken(
            user: $user,
            system: $system
        );
    }
}
