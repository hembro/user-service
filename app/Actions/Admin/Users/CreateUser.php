<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\CreateUserCommand;
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

    public function handle(CreateUserCommand $command): User
    {
        Roles::ensureBelongsToSystem($command->roles, $command->system);

        return $this->db->transaction(
            callback: function () use ($command): User {

                /** @var User $user */
                $user = User::create([
                    'email' => $command->email,
                    'password' => $command->password,
                    'status' => UserStatus::ACTIVE,
                ]);

                $user->profile()->create(
                    attributes: $command->toProfileAttributes()
                );

                $user->assignRole($command->roles);

                UserInvited::dispatch($user, $command->actor, $command->system);

                return $user->load(['profile', 'roles', 'permissions']);
            }
        );
    }
}
