<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\TwoFactorDisabled;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogTwoFactorDisabled implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(TwoFactorDisabled $event): void
    {
        Log::channel('auth')->info(
            message: 'user disabled two-factor authentication',
            context: [
                'user_id' => $event->user->id,
                'ip' => $event->metadata->ip,
                'user_agent' => $event->metadata->userAgent,
                'timestamp' => Carbon::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
            ]
        );
    }
}
