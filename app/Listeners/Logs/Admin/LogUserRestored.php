<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserRestored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserRestored implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserRestored $event): void
    {
        $this->logger->info(
            message: 'audit: admin restored user',
            context: [
                'user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
