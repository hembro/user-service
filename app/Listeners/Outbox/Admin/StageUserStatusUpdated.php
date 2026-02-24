<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserStatusUpdated;
use App\Messages\Integration\Admin\UserStatusUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserStatusUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserStatusUpdated $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_STATUS_UPDATED,
            message: UserStatusUpdatedMessage::make($event->targetUser, $event->actor, $event->oldStatus, $event->newStatus, $event->system)
        );
    }
}
