<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserUpdated;
use App\Messages\Integration\Admin\UserUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class WriteUserUpdatedToOutbox
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserUpdated $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_UPDATED,
            message: UserUpdatedMessage::make($event->targetUser, $event->actor, $event->changes, $event->system)
        );
    }
}
