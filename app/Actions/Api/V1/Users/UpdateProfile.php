<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\UpdateProfileDTO;
use App\Events\Users\UserProfileUpdated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateProfile
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateProfileDTO $dto, User $user): void
    {
        $this->db->transaction(
            callback: function () use ($user, $dto) {

                $profile = $user->profile;

                $profile->fill(
                    attributes: $dto->toProfileAttributes()
                );

                $changes = [];
                if ($profile->isDirty()) {
                    foreach ($profile->getDirty() as $key => $value) {
                        $changes[$key] = [
                            'old' => $profile->getOriginal($key),
                            'new' => $value,
                        ];
                    }

                    $profile->save();

                    $this->db->afterCommit(
                        fn () => UserProfileUpdated::dispatch($user, $changes)
                    );
                }
            }
        );
    }
}
