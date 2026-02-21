<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserVerified;
use App\Messages\Integration\Auth\UserVerifiedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserVerified
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserVerified $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_USER_VERIFIED,
            message: UserVerifiedMessage::make($event->user, $event->system)
        );
    }
}
