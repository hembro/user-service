<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\UpdateProfileCommand;
use App\Events\Users\UserProfileUpdated;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateProfile
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateProfileCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command) {

                $profile = $command->user->profile;

                $profile->fill(
                    attributes: $command->toProfileAttributes()
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

                    UserProfileUpdated::dispatch($command->user, $changes, $command->system);
                }
            }
        );
    }
}
