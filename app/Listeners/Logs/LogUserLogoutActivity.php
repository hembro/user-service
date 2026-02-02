<?php

declare(strict_types=1);

namespace App\Listeners\Logs;

use App\Events\UserLoggedOut;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class LogUserLogoutActivity implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserLoggedOut $event): void
    {
        $this->logger->info('audit: user logged-out', [
            'user_id' => $event->user->id,
            'ip' => $event->metadata->ip,
            'user_agent' => $event->metadata->userAgent,
            'timestamp' => CarbonImmutable::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
        ]);
    }
}
