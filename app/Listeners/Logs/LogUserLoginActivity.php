<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\UserLoggedIn;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserLoginActivity implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $occurredAt = CarbonImmutable::createFromTimestamp($event->metadata->timestamp);

        $event->user->updateQuietly(['last_login_at' => $occurredAt]);

        $this->logger->info('audit: user logged-in', [
            'user_id' => $event->user->id,
            'ip' => $event->metadata->ip,
            'user_agent' => $event->metadata->userAgent,
            'timestamp' => $occurredAt->toIso8601String(),
        ]);
    }
}
