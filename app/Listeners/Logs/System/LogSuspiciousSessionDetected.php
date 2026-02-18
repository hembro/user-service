<?php

declare(strict_types=1);

namespace App\Listeners\Logs\System;

use App\Events\Auth\SuspiciousSessionDetected;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogSuspiciousSessionDetected implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(SuspiciousSessionDetected $event): void
    {
        Log::channel('system')->info(
            message: 'suspicious session detected',
            context: [
                'user_id' => $event->user->id,
                'ip' => $event->metadata->ip,
                'user_agent' => $event->metadata->userAgent,
                'timestamp' => CarbonImmutable::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
            ]
        );
    }
}
