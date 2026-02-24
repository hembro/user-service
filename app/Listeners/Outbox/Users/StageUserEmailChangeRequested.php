<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserEmailChangeRequested;
use App\Messages\Integration\Users\UserEmailChangeRequestedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserEmailChangeRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChangeRequested $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_EMAIL_CHANGE_REQUESTED,
            message: UserEmailChangeRequestedMessage::make($event->user, $event->token, $event->newEmail, $event->system, $event->metadata)
        );
    }
}
