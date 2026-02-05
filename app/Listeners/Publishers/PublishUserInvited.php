<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\UserInvited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserInvited implements ShouldQueue
{
    public $tries = 5;

    public $backoff = [10, 30, 60];

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
