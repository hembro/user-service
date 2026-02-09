<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserPasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserPasswordReset implements ShouldQueue
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserPasswordReset $event): void
    {
        $this->logger->info(
            message: 'audit: admin reset user password',
            context: [
                'user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
