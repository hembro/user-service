<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\UserLoggedOut;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserLoggedout implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserLoggedOut $event): void
    {
        Log::channel('audit')->info(
            message: 'user logged-out',
            context: [
                'user_id' => $event->user->id,
                'ip' => $event->metadata->ip,
                'user_agent' => $event->metadata->userAgent,
                'timestamp' => CarbonImmutable::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
            ]
        );
    }
}
