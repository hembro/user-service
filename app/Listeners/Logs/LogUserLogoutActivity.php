<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\UserLoggedOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserLogoutActivity implements ShouldQueue
{
    public $queue = 'low-priority';

    public $tries = 3;

    public function handle(UserLoggedOut $event): void
    {
        Log::info('audit: user logged-out', [
            'user_id' => $event->user->id,
            'ip' => $event->ip,
            'user_agent' => $event->userAgent,
            'timestamp' => $event->timestamp,
        ]);
    }
}
