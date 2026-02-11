<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Admin;

use App\Events\Admin\UserInvited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserInvited implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserInvited $event): void
    {
        $this->logger->info('broker: UserInvited event', [
            'user_id' => $event->user->id,
        ]);
    }
}
