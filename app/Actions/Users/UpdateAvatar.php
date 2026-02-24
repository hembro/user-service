<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\UpdateAvatarCommand;
use App\Events\Users\UserAvatarUpdated;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateAvatar
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateAvatarCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command) {

                $profile = $command->user->profile;

                if ($profile->avatar_path && Storage::disk('public')->exists($profile->avatar_path)) {
                    Storage::disk('public')->delete($profile->avatar_path);
                }

                $path = $command->file->store(
                    path: "avatars/{$command->user->id}",
                    options: 'public'
                );

                $profile->update([
                    'avatar_path' => $path,
                ]);

                UserAvatarUpdated::dispatch($command->user, $command->system);
            }
        );
    }
}
