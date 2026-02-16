<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\DTOs\Api\V1\Users\RegisterUserDTO;
use App\Enums\UserStatus;
use App\Events\Users\UserRegistered;
use App\Models\User;
use App\Services\Auth\DeviceTrustService;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class RegisterUser
{
    public function __construct(
        private DatabaseManager $db,
        private DeviceTrustService $deviceService,
        private LoggerInterface $logger
    ) {}

    public function handle(RegisterUserDTO $dto, string $deviceId, RequestMetadata $metadata): User
    {
        return $this->db->transaction(
            callback: function () use ($dto, $deviceId, $metadata): User {

                $this->logger->debug(
                    message: 'user registration initiated',
                    context: ['email' => $dto->email]
                );

                $user = User::query()->create([
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'status' => UserStatus::PENDING,
                ]);

                $user->profile()->create(
                    attributes: $dto->toProfileAttributes()
                );

                $user->assignRole($dto->system->defaultRole());

                $user->load(['profile', 'roles.permissions', 'permissions']);

                $this->db->afterCommit(
                    function () use ($user, $deviceId, $metadata) {
                        UserRegistered::dispatch($user);
                        $this->deviceService->trustDevice($user, $deviceId, $metadata);
                    }
                );

                return $user;
            }
        );
    }
}
