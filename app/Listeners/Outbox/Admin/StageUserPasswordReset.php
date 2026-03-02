<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Events\Admin\UserPasswordReset;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionOccurredEvent;

final readonly class StageUserPasswordReset
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserPasswordReset $event): void
    {
        $event->actor->loadMissing('profile');
        $event->targetUser->loadMissing('profile');

        $routingKey = UserRoutingKey::ACTION_OCCURRED;

        $actor = UserIntegrationMapper::toActor($event->actor);
        $target = UserIntegrationMapper::toTarget($event->targetUser);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new ActionOccurredEvent(
                actor: $actor,
                type: UserActionType::PASSWORD_RESET,
                target: $target,
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
