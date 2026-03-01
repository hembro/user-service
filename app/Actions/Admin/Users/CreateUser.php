<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\CreateUserCommand;
use App\Enums\Roles;
use App\Events\Admin\UserInvited;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\UserStatus;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class CreateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(CreateUserCommand $command): User
    {
        Roles::ensureBelongsToSystem($command->roles, $command->system);

        try {
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

                    UserInvited::dispatch($user, $command->actor, $command->system, $command->metadata);

                    return $user->load(['profile', 'roles', 'permissions']);
                }
            );
        } catch (Throwable $exception) {
            $this->logger->critical('Admin User create transaction failed.', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }
}
