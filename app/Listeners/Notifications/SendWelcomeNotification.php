<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class SendWelcomeNotification implements ShouldQueue
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(Verified $event): void
    {
        $this->logger->info("Sending welcome email to {$event->user->email}");
    }
}
