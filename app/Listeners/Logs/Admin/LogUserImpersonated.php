<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserImpersonated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserImpersonated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserImpersonated $event): void
    {
        $this->logger->warning(
            message: 'audit: admin impersonated user',
            context: [
                'user_id' => $event->targetUser->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
