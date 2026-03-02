<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Events\Auth\EnableTwoFactorRequested;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionRequestType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionRequestedEvent;

final readonly class StageEnableTwoFactorRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(EnableTwoFactorRequested $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = UserRoutingKey::ACTION_REQUESTED;

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new ActionRequestedEvent(
                actor: $actor,
                target: $target,
                action: new ActionRequest(
                    type: UserActionRequestType::TWO_FACTOR_SETUP
                )
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
