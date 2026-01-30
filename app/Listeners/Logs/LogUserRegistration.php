<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\UserCreated;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserRegistration implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public $queue = 'low-priority';

    public function handle(UserCreated $event): void
    {
        Log::info('audit: user registered', [
            'user_id' => $event->user->id,
        ]);
    }
}
