<?php

declare(strict_types=1);

namespace App\Listeners\Logs\User;

use App\Events\UserRegistered;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserRegistered implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->logger->info('audit: user registered', [
            'user_id' => $event->user->id,
        ]);
    }
}
