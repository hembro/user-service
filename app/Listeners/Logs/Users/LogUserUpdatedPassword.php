<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserPasswordUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserUpdatedPassword implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserPasswordUpdated $event): void
    {
        Log::channel('audit')->info(
            message: 'audit: user updated password',
            context: [
                'user_id' => $event->user->id,
            ]
        );
    }
}
