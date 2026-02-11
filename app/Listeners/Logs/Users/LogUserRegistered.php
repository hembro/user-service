<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserRegistered implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->logger->info(
            message: 'audit: user registered',
            context: [
                'user_id' => $event->user->id,
            ]
        );
    }
}
