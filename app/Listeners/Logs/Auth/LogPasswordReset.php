<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogPasswordReset implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(PasswordReset $event): void
    {
        $this->logger->info(
            message: 'audit: user reset password (recovery)',
            context: [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }
}
