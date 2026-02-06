<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\UserInvited;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserCreated implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserInvited $event): void
    {
        $this->logger->info('audit: admin created user', [
            'target_user_id' => $event->user->id,
            'admin_id' => $event->admin->id,
        ]);
    }
}
