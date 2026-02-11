<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\SocialUserDTO;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Users\UserRegistered;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class CreateSocialUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(SocialUserDTO $dto, Systems $system): User
    {
        return $this->db->transaction(
            callback: function () use ($dto, $system) {

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

                    $this->logger->debug(
                        message: 'social user registration initiated',
                        context: ['email' => $dto->email]
                    );

                    $user->profile()->create([
                        'first_name' => $dto->firstName,
                        'last_name' => $dto->lastName,
                        'avatar_path' => $dto->avatarPath,
                        'sex' => 'unknown',
                    ]);

                    $user->assignRole($system->defaultRole());

                    $this->db->afterCommit(
                        fn () => UserRegistered::dispatch($user)
                    );
                }

                $user->socialAccounts()->firstOrCreate(
                    attributes: ['provider_name' => $dto->provider->value],
                    values: ['provider_id' => $dto->providerId]
                );

                return $user;
            }
        );
    }
}
