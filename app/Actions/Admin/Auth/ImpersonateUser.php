<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Commands\Admin\Auth\ImpersonateUserCommand;
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

    public function handle(ImpersonateUserCommand $command): TokenDTO
    {
        $token = $this->tokenIssuer->issueFullToken($command->targetUser, $command->system);

        $this->db->transaction(
            callback: function () use ($command) {
                UserImpersonated::dispatch($command->targetUser, $command->actor, $command->system);
            }
        );

        return $token;
    }
}
