<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogPasswordReset implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(PasswordReset $event): void
    {
        Log::channel('auth')->info(
            message: 'user reset password (recovery)',
            context: [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }
}
