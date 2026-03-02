<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Events\Admin\UserInvited;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserCreatedEvent;

final readonly class StageUserInvited
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserInvited $event): void
    {
        $event->actor->loadMissing('profile');
        $event->targetUser->loadMissing('profile');

        $routingKey = UserRoutingKey::USER_CREATED;

        $actor = UserIntegrationMapper::toActor($event->actor);
        $target = UserIntegrationMapper::toTarget($event->targetUser);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new UserCreatedEvent(
                actor: $actor,
                target: $target,
                occurredAt: $event->targetUser->created_at->toIso8601String(),
                reason: $event->reason,
                systemContext: $event->systemContext
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
