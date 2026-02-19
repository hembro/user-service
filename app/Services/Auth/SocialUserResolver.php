<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Api\V1\Auth\SocialUserDTO;
use App\Enums\Sex;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class SocialUserResolver
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function resolve(SocialUserDTO $dto, Systems $system): User
    {
        return $this->db->transaction(
            callback: function () use ($dto, $system) {

                $this->logger->debug(
                    message: 'social user registration initiated',
                    context: ['email' => $dto->email]
                );

                $user = User::query()
                    ->firstOrCreate(
                        attributes: ['email' => $dto->email],
                        values: [
                            'password' => null,
                            'status' => UserStatus::ACTIVE,
                            'email_verified_at' => now(),
                        ]
                    );

                if ($user->wasRecentlyCreated) {

                    $this->logger->info('user registered via social', ['user_id' => $user->id]);

                    $user->profile()->create([
                        'first_name' => $dto->firstName,
                        'last_name' => $dto->lastName,
                        'avatar_path' => $dto->avatarPath,
                        'sex' => Sex::UNKNOWN,
                    ]);

                    $user->assignRole($system->defaultRole());
                }

                $user->socialAccounts()->firstOrCreate(
                    attributes: [
                        'provider_name' => $dto->provider->value,
                        'provider_id' => $dto->providerId,
                    ]
                );

                return $user;
            }
        );
    }
}
