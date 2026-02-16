<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use App\Models\User;
use App\Services\Auth\TokenIssuer;

final readonly class ImpersonateUser
{
    public function __construct(
        private TokenIssuer $tokenIssuer,
    ) {}

    public function handle(User $target, Systems $system): TokenDTO
    {
        return $this->tokenIssuer->issueFullToken(
            user: $target,
            system: $system
        );
    }
}
