<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\Admin\UserUpdated;
use App\Events\Users\UserUpdatedProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserUpdated implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserUpdated|UserUpdatedProfile $event): void
    {
        $this->logger->info('broker: UserUpdated event', [
            'user_id' => $event->user->id,
        ]);
    }
}
