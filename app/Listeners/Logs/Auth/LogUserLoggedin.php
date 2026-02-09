<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\UserLoggedIn;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserLoggedin implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $occurredAt = CarbonImmutable::createFromTimestamp($event->metadata->timestamp);

        $event->user->update(['last_login_at' => $occurredAt]);

        $this->logger->info('audit: user logged-in', [
            'user_id' => $event->user->id,
            'ip' => $event->metadata->ip,
            'user_agent' => $event->metadata->userAgent,
            'timestamp' => $occurredAt->toIso8601String(),
        ]);
    }
}
