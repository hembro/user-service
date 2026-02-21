<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Auth;

use App\DTOs\Api\V1\Admin\Users\ImpersonateUserData;
use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Events\Admin\UserImpersonated;
use App\Services\Auth\TokenIssuer;
use Illuminate\Database\DatabaseManager;

final readonly class ImpersonateUser
{
    public function __construct(
        private TokenIssuer $tokenIssuer,
        private DatabaseManager $db
    ) {}

    public function handle(ImpersonateUserData $dto): TokenDTO
    {
        $token = $this->tokenIssuer->issueFullToken(
            user: $dto->targetUser,
            system: $dto->system
        );

        $this->db->transaction(
            callback: function () use ($dto) {
                UserImpersonated::dispatch($dto->targetUser, $dto->actor, $dto->system);
            }
        );

        return $token;
    }
}
