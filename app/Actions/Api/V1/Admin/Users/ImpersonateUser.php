<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Events\Admin\UserImpersonated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class ImpersonateUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(User $admin, User $targetUser): TokenDTO
    {
        return $this->db->transaction(
            callback: function () use ($admin, $targetUser) {

                $tokenResult = $targetUser->createToken(
                    name: "Impersonation by Admin {$admin->id}",
                    scopes: $targetUser->roles->pluck('name')->toArray()
                );

                $this->db->afterCommit(
                    fn () => UserImpersonated::dispatch($targetUser, $admin)
                );

                return new TokenDTO(
                    tokenType: 'Bearer',
                    accessToken: $tokenResult->accessToken,
                    refreshToken: '',
                    expiresIn: $tokenResult->expiresIn
                );
            }
        );
    }
}
