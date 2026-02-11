<?php

declare(strict_types=1);

namespace App\Listeners\Publishers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class PublishPasswordReset implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(PasswordReset $event): void
    {
        $this->logger->info(
            message: 'broker: PasswordReset event',
            context: [
                'user_id' => $event->user->id,
                'source' => 'forgot_password_flow',
            ]
        );
    }
}
