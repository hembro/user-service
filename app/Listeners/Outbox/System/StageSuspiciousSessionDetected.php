<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\System;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\SuspiciousSessionDetected;
use App\Messages\Integration\System\SuspiciousSessionDetectedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageSuspiciousSessionDetected
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(SuspiciousSessionDetected $event): void
    {
        $this->outbox->publish(
            routingKey: RoutingKey::SYSTEM_SUSPICIOUS_SESSION,
            message: SuspiciousSessionDetectedMessage::make($event->user, $event->system, $event->metadata)
        );
    }
}
