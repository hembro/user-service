<?php

declare(strict_types=1);

namespace App\Listeners\Logs\User;

use App\Events\UserUpdatedProfile;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserProfileUpdated implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserUpdatedProfile $event): void
    {
        $this->logger->info('audit: user updated profile', [
            'user_id' => $event->user->id,
        ]);
    }
}
