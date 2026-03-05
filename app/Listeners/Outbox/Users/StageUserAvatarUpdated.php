<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Events\Users\UserAvatarUpdated;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserAvatarUpdatedEvent;

final readonly class StageUserAvatarUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserAvatarUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = UserRoutingKey::USER_AVATAR_UPDATED;

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new UserAvatarUpdatedEvent(
                actor: $actor,
                target: $target,
                oldAvatarUrl: $event->oldAvatarUrl,
                newAvatarUrl: $event->user->profile?->avatarUrl,
                occurredAt: $event->user->updated_at->toIso8601String(),
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
