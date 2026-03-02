<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Events\Admin\UserDeleted;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserDeletedEvent;

final readonly class StageUserDeleted
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserDeleted $event): void
    {
        $event->actor->loadMissing('profile');

        $routingKey = UserRoutingKey::USER_DELETED;

        $actor = UserIntegrationMapper::toActor($event->actor);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new UserDeletedEvent(
                actor: $actor,
                userId: $event->userId,
                occurredAt: now()->toIso8601String(),
                reason: 'admin requested deletion'
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
