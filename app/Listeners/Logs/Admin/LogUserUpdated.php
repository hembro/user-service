<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserUpdated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserUpdated $event): void
    {
        $this->logger->info('audit: admin updated user', [
            'user_id' => $event->user->id,
            'admin_id' => $event->admin->id,
            'changes' => $event->changes,
        ]);
    }
}
