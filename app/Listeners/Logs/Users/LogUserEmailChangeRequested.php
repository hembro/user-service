<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserEmailChangeRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserEmailChangeRequested implements ShouldQueue
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(UserEmailChangeRequested $event): void
    {
        $this->logger->info('audit: user requested email change', [
            'user_id' => $event->user->id,
            'new_email' => $event->newEmail,
        ]);
    }
}
