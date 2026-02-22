<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\RegisterUserCommand;
use App\Contracts\Auth\DeviceTrustVerifier;
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

    public function handle(RegisterUserCommand $command): User
    {
        return $this->db->transaction(
            callback: function () use ($command): User {

                $this->logger->debug(
                    message: 'user registration initiated',
                    context: ['email' => $command->email]
                );

                $user = User::query()->create([
                    'email' => $command->email,
                    'password' => $command->password,
                    'status' => UserStatus::PENDING,
                ]);

                $user->profile()->create(
                    attributes: $command->toProfileAttributes()
                );

                $user->assignRole($command->system->defaultRole());

                $this->deviceService->trustDevice($user, $command->deviceId, $command->metadata);

                $verificationUrl = $this->linkGenerator->generate($user);

                $user->load(['profile', 'roles.permissions', 'permissions']);

                UserRegistered::dispatch($user, $verificationUrl, $command->system);

                return $user;
            }
        );
    }
}
