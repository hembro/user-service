<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserAvatarUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserAvatarUpdated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserAvatarUpdated $event): void
    {
        Log::channel('audit')->info(
            message: 'user updated avatar',
            context: [
                'user_id' => $event->user->id,
                'avatar_path' => $event->user->profile->avatar_path,
            ]
        );
    }
}
