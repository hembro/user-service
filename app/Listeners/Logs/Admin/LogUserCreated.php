<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserInvited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserCreated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserInvited $event): void
    {
        $this->logger->info(
            message: 'audit: admin created user',
            context: [
                'target_user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
