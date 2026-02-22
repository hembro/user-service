<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserLoggedIn;
use App\Messages\Integration\Auth\UserLoggedInMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserLoggedIn
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_USER_LOGGED_IN,
            message: UserLoggedInMessage::make($event->user, $event->deviceId, $event->system, $event->metadata)
        );
    }
}
