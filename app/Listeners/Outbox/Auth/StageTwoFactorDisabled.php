<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\TwoFactorDisabled;
use App\Messages\Integration\Auth\TwoFactorDisabledMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageTwoFactorDisabled
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(TwoFactorDisabled $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_TWO_FACTOR_DISABLED,
            message: TwoFactorDisabledMessage::make($event->user, $event->system, $event->metadata)
        );
    }
}
