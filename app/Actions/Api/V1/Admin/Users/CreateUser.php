<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\CreateUserData;
use App\Enums\Roles;
use App\Enums\UserStatus;
use App\Events\Admin\UserInvited;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class CreateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(CreateUserData $dto): User
    {
        Roles::ensureBelongsToSystem($dto->roles, $dto->system);

        return $this->db->transaction(
            callback: function () use ($dto): User {

                $this->logger->debug(
                    message: 'admin user creation initiated',
                    context: ['admin_id' => (string) $dto->actor->id, 'email' => $dto->email]
                );

                /** @var User $user */
                $user = User::create([
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'status' => UserStatus::ACTIVE,
                ]);

                $user->profile()->create(
                    attributes: $dto->toProfileAttributes()
                );

                $user->assignRole($dto->roles);

                UserInvited::dispatch($user, $dto->actor, $dto->system);

                return $user->load(['profile', 'roles', 'permissions']);
            }
        );
    }
}
