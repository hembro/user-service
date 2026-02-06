<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\Auth\UserLoggedOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserLoggedOut implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserLoggedOut $event): void
    {
        $this->logger->info('broker: UserLoggedOut event', [
            'user_id' => $event->user->id,
        ]);
    }
}
