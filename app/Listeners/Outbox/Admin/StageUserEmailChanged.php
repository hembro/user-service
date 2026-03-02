<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Events\Admin\UserEmailChanged;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserProfileUpdatedEvent;

final readonly class StageUserEmailChanged
{
    public function __construct(
        public OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChanged $event): void
    {
        $event->targetUser->loadMissing('profile');
        $event->actor->loadMissing('profile');

        $routingKey = UserRoutingKey::USER_PROFILE_UPDATED;

        $actor = UserIntegrationMapper::toActor($event->actor);
        $target = UserIntegrationMapper::toTarget($event->targetUser);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new UserProfileUpdatedEvent(
                actor: $actor,
                target: $target,
                changes: [
                    'email' => $event->emailChanges,
                ],
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
