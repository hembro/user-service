<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserLoggedOut;
use App\Messages\Integration\Auth\LoggedOutMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserLoggedOut
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserLoggedOut $event): void
    {
        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_LOGGED_OUT,
            message: LoggedOutMessage::make($event->user, $event->metadata, $event->system)
        );
    }
}
