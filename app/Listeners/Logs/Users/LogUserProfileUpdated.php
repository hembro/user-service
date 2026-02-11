<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserProfileUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserProfileUpdated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserProfileUpdated $event): void
    {
        $this->logger->info(
            message: 'audit: user updated profile',
            context: [
                'user_id' => $event->user->id,
                'changes' => $event->changes,
            ]
        );
    }
}
