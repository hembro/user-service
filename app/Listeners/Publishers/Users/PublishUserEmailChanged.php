<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Users;

use App\Events\Users\UserEmailChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishUserEmailChanged implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserEmailChanged $event): void
    {
        $this->logger->info(
            message: 'broker: UserEmailChanged event',
            context: [
                'user_id' => $event->user->id,
                'old_email' => $event->oldEmail,
                'new_email' => $event->user->email,
            ]
        );
    }
}
