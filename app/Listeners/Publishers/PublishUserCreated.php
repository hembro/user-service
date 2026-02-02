<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\UserCreated;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserCreated implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public $tries = 5;

    public $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserCreated $event): void
    {
        $this->logger->info('broker: UserCreated event', [
            'user_id' => $event->user->id,
        ]);
    }
}
