<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\RegisterUserDTO;
use App\Enums\UserStatus;
use App\Events\Users\UserRegistered;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class RegisterUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(RegisterUserDTO $dto): User
    {
        return $this->db->transaction(
            callback: function () use ($dto): User {

                $this->logger->debug(
                    message: 'user registration initiated',
                    context: ['email' => $dto->email]
                );

                $user = User::query()->create([
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'status' => UserStatus::PENDING,
                ]);

                $user->profile()->create([
                    'title' => $dto->title,
                    'first_name' => $dto->firstName,
                    'middle_name' => $dto->middleName,
                    'last_name' => $dto->lastName,
                    'suffix' => $dto->suffix,
                    'sex' => $dto->sex,
                    'mobile_number' => $dto->mobileNumber,
                    'preferences' => $dto->preferences,
                ]);

                $user->assignRole($dto->system->defaultRole());

                $this->db->afterCommit(
                    fn () => UserRegistered::dispatch($user)
                );

                return $user->load(['profile', 'roles.permissions', 'permissions']);
            }
        );
    }
}
