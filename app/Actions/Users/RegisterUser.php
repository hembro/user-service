<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\RegisterUserCommand;
use App\Contracts\Auth\DeviceTrustVerifier;
use App\Events\Users\UserRegistered;
use App\Models\User;
use App\Services\Auth\VerificationLinkGenerator;
use App\Services\Users\SystemRoleResolver;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class RegisterUser
{
    public function __construct(
        private DatabaseManager $db,
        private DeviceTrustVerifier $deviceService,
        private VerificationLinkGenerator $linkGenerator,
        private SystemRoleResolver $roleResolver,
        private LoggerInterface $logger
    ) {}

    public function handle(RegisterUserCommand $command): User
    {
        try {
            return $this->db->transaction(
                callback: function () use ($command): User {

                    $user = User::query()->create([
                        'email' => $command->email,
                        'password' => $command->password,
                        'status' => UserStatus::PENDING,
                    ]);

                    $user->profile()->create(
                        attributes: $command->toProfileAttributes()
                    );

                    $user->assignRole($this->roleResolver->defaultRoleFor($command->system));

                    $this->deviceService->trustDevice($user, $command->deviceId);

                    $verificationUrl = $this->linkGenerator->generate($user);

                    $user->load(['profile', 'roles.permissions', 'permissions']);

                    UserRegistered::dispatch($user, $command->system, $verificationUrl, $command->systemContext);

                    return $user;
                }
            );
        } catch (Throwable $exception) {
            $this->logger->critical('User registration transaction failed.', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }
}
