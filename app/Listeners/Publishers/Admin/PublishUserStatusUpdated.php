<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Admin;

use App\Events\Admin\UserStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserStatusUpdated implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserStatusUpdated $event): void
    {
        $this->logger->info('broker: UserStatusUpdated event', [
            'user_id' => $event->user->id,
        ]);
    }
}
