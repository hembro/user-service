<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Users;

use App\Events\Users\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserRegistered implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->logger->info(
            message: 'broker: UserRegistered event',
            context: [
                'user_id' => $event->user->id,
                'source' => 'public_registration',
            ]
        );
    }
}
