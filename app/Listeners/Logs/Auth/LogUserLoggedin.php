<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\UserLoggedIn;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserLoggedin implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserLoggedIn $event): void
    {
        $occurredAt = CarbonImmutable::createFromTimestamp($event->metadata->timestamp);

        $event->user->update(['last_login_at' => $occurredAt]);

        Log::channel('auth')->info(
            message: 'device usage heartbeat',
            context: [
                'user_id' => $event->user->id,
                'device_id' => $event->deviceId,
                'ip' => $event->metadata->ip,
                'user_agent' => $event->metadata->userAgent,
                'timestamp' => $occurredAt->toIso8601String(),
            ]
        );
    }
}
