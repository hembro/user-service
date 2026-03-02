<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Events\Admin\UserStatusUpdated;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserStatusChangedEvent;

final readonly class StageUserStatusUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserStatusUpdated $event): void
    {
        $event->actor->loadMissing('profile');
        $event->targetUser->loadMissing('profile');

        $routingKey = UserRoutingKey::USER_STATUS_UPDATED;

        $actor = UserIntegrationMapper::toActor($event->actor);
        $target = UserIntegrationMapper::toTarget($event->targetUser);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new UserStatusChangedEvent(
                actor: $actor,
                target: $target,
                oldStatus: $event->oldStatus,
                newStatus: $event->targetUser->status,
                occurredAt: $event->targetUser->updated_at->toIso8601String(),
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
