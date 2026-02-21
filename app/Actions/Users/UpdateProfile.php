<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\Api\V1\Users\UpdateProfileData;
use App\Events\Users\UserProfileUpdated;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateProfile
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateProfileData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto) {

                $profile = $dto->user->profile;

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

                    UserProfileUpdated::dispatch($dto->user, $changes, $dto->system);
                }
            }
        );
    }
}
