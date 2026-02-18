<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\RecoveryCodesRegenerated;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogRecoveryCodeRegenerated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(RecoveryCodesRegenerated $event): void
    {
        Log::channel('auth')->info(
            message: 'user regenerated recovery codes',
            context: [
                'user_id' => $event->user->id,
                'ip' => $event->metadata->ip,
                'user_agent' => $event->metadata->userAgent,
                'timestamp' => Carbon::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
            ]
        );
    }
}
