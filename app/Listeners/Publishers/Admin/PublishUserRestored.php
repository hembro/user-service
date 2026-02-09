<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Admin;

use App\Events\Admin\UserRestored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserRestored implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserRestored $event): void
    {
        $this->logger->info('broker: UserRestored event', [
            'user_id' => $event->user->id,
        ]);
    }
}
