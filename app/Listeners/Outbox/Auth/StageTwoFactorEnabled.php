<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\TwoFactorEnabled;
use App\Messages\Integration\Auth\TwoFactorEnabledMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageTwoFactorEnabled
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(TwoFactorEnabled $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_TWO_FACTOR_ENABLED,
            message: TwoFactorEnabledMessage::make($event->user, $event->system)
        );
    }
}
