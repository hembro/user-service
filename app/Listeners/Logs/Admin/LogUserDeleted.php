<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserDeleted;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserDeleted implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserDeleted $event): void
    {
        $this->logger->info('audit: admin deleted user', [
            'system' => $event->dto->system->value,
            'user_email' => $event->userEmail,
            'admin_id' => $event->admin->id,
        ]);
    }
}
