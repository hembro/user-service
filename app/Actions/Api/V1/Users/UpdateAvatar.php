<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\UpdateAvatarData;
use App\Events\Users\UserAvatarUpdated;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateAvatar
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateAvatarData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto) {

                $profile = $dto->user->profile;

                if ($profile->avatar_path && Storage::disk('public')->exists($profile->avatar_path)) {
                    Storage::disk('public')->delete($profile->avatar_path);
                }

                $path = $dto->file->store(
                    path: "avatars/{$dto->user->id}",
                    options: 'public'
                );

                $profile->update([
                    'avatar_path' => $path,
                ]);

                UserAvatarUpdated::dispatch($dto->user, $dto->system);
            }
        );
    }
}
