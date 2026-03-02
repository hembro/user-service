<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\SocialProfile;
use App\Enums\Sex;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use Psr\Log\LoggerInterface;

final readonly class SocialUserResolver
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function resolve(SocialProfile $profile, Systems $system): User
    {
        return $this->db->transaction(
            callback: function () use ($profile, $system) {

                $user = User::query()
                    ->firstOrCreate(
                        attributes: ['email' => $profile->email],
                        values: [
                            'password' => null,
                            'status' => UserStatus::ACTIVE,
                            'email_verified_at' => now(),
                        ]
                    );

                if ($user->wasRecentlyCreated) {

                    $this->logger->info('user registered via social', ['user_id' => $user->id]);

                    $user->profile()->create([
                        'first_name' => $profile->firstName,
                        'last_name' => $profile->lastName,
                        'avatar_path' => $profile->avatarPath,
                        'sex' => Sex::UNKNOWN,
                    ]);

                    $user->assignRole($system->defaultRole());
                }

                $user->socialAccounts()->firstOrCreate(
                    attributes: [
                        'provider_name' => $profile->provider->value,
                        'provider_id' => $profile->providerId,
                    ]
                );

                return $user;
            }
        );
    }
}
