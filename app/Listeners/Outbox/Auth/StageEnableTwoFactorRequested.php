<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\EnableTwoFactorRequested;
use App\Messages\Integration\Auth\EnableTwoFactorRequestedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageEnableTwoFactorRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(EnableTwoFactorRequested $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_TWO_FACTOR_REQUESTED,
            message: EnableTwoFactorRequestedMessage::make($event->user, $event->system, $event->metadata)
        );
    }
}
