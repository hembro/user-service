<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\AdminUpdatedUser;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogAdminUpdatedUserActivity implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(AdminUpdatedUser $event): void
    {
        $this->logger->info('audit: admin updated user', [
            'target_user_id' => $event->user->id,
            'admin_id' => $event->admin->id,
            'changes' => $event->changes,
        ]);
    }
}
