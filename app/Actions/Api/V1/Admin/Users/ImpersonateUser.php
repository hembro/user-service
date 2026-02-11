<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use App\Events\Admin\UserImpersonated;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Database\DatabaseManager;

final readonly class ImpersonateUser
{
    public function __construct(
        private DatabaseManager $db,
        private AuthService $auth
    ) {}

    public function handle(User $admin, User $target, Systems $system): TokenDTO
    {
        return $this->db->transaction(
            callback: function () use ($admin, $target, $system) {

                $token = $this->auth->impersonate(
                    admin: $admin,
                    target: $target,
                    system: $system
                );

                $this->db->afterCommit(
                    fn () => UserImpersonated::dispatch($target, $admin)
                );

                return new TokenDTO(
                    tokenType: 'Bearer',
                    accessToken: $token->accessToken,
                    refreshToken: '',
                    expiresIn: $token->expiresIn
                );
            }
        );
    }
}
