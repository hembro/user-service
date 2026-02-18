<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserRegistered implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserRegistered $event): void
    {
        Log::channel('audit')->info(
            message: 'user registered',
            context: [
                'user_id' => $event->user->id,
            ]
        );
    }
}
