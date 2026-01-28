<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\RegisterUserDTO;
use App\Enums;
use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class CreateUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(RegisterUserDTO $dto): User
    {
        return $this->db->transaction(
            callback: function () use ($dto): User {

                $user = User::create([
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'status' => Enums\UserStatus::PENDING,
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

                $user->assignRole($this->resolveRole($dto->system));

                UserCreated::dispatch($user);

                Log::info(
                    message: 'User created',
                    context: ['user_id' => $user->id, 'system' => $dto->system]
                );

                return $user;
            }
        );
    }

    private function resolveRole(string $system): Enums\Roles
    {
        return match ($system) {
            Enums\Systems::PMS->value => Enums\Roles::PMS_PROPONENT,
            Enums\Systems::HERDIN->value => Enums\Roles::HERDIN_USER,
            Enums\Systems::PHRR->value => Enums\Roles::PHRR_USER,
            default => throw new InvalidArgumentException("Invalid system: {$system}"),
        };
    }
}
