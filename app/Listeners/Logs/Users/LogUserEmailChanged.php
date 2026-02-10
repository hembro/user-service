<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserEmailChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserEmailChanged implements ShouldQueue
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(UserEmailChanged $event): void
    {
        $this->logger->warning('audit: user confirmed email change', [
            'user_id' => $event->user->id,
            'old_email' => $event->oldEmail,
            'new_email' => $event->user->email,
        ]);
    }
}
