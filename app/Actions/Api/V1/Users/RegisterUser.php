<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Users\RegisterUserData;
use App\Enums\UserStatus;
use App\Events\Users\UserRegistered;
use App\Models\User;
use App\Services\Auth\VerificationLinkGenerator;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class RegisterUser
{
    public function __construct(
        private DatabaseManager $db,
        private DeviceTrustVerifier $deviceService,
        private VerificationLinkGenerator $linkGenerator,
        private LoggerInterface $logger
    ) {}

    public function handle(RegisterUserData $dto): User
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

                $user->profile()->create(
                    attributes: $dto->toProfileAttributes()
                );

                $user->assignRole($dto->system->defaultRole());

                $this->deviceService->trustDevice($user, $dto->deviceId, $dto->metadata);

                $verificationUrl = $this->linkGenerator->generate($user);

                $user->load(['profile', 'roles.permissions', 'permissions']);

                UserRegistered::dispatch($user, $verificationUrl, $dto->system);

                return $user;
            }
        );
    }
}
