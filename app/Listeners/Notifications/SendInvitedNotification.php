<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\Admin\UserInvited;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class SendInvitedNotification implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'high';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserInvited $event): void
    {
        $this->logger->info("Sending invited email to {$event->user->email}, invited by {$event->admin->profile->full_name}");
    }
}
