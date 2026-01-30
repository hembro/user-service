<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\UserLoggedIn;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserLoginActivity implements ShouldQueue
{
    public $queue = 'low-priority';

    public $tries = 3;

    public function handle(UserLoggedIn $event): void
    {
        $event->user->updateQuietly(['last_login_at' => Carbon::parse($event->timestamp)]);

        Log::info('audit: user logged-in', [
            'user_id' => $event->user->id,
            'ip' => $event->ip,
            'user_agent' => $event->userAgent,
            'timestamp' => $event->timestamp,
        ]);
    }
}
