<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\Admin\UserDeleted;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserDeleted implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserDeleted $event): void
    {
        $this->logger->info('broker: UserDeleted event', [
            'user_email' => $event->userEmail,
        ]);
    }
}
