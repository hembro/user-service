<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\CreateUserDTO;
use App\Enums\UserStatus;
use App\Events\UserInvited;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;

final readonly class AdminCreateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(CreateUserDTO $dto, User $admin): User
    {
        return $this->db->transaction(
            callback: function () use ($dto, $admin): User {

                /** @var User $user */
                $user = User::create([
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'status' => UserStatus::ACTIVE,
                ]);

                $user->profile()->create([
                    'title' => $dto->title,
                    'first_name' => $dto->firstName,
                    'last_name' => $dto->lastName,
                    'middle_name' => $dto->middleName,
                    'suffix' => $dto->suffix,
                    'sex' => $dto->sex,
                    'mobile_number' => $dto->mobileNumber,
                    'preferences' => $dto->preferences,
                ]);

                $user->assignRole($dto->role);

                $this->logger->debug(
                    message: 'admin user creation initiated',
                    context: ['admin_id' => $admin->id, 'email' => $dto->email]
                );

                Cache::tags([
                    'users_index',
                    "users_index.{$dto->system->value}",
                ])->flush();

                UserInvited::dispatch($user, $admin);

                return $user->load(['profile', 'roles', 'permissions']);
            }
        );
    }
}
