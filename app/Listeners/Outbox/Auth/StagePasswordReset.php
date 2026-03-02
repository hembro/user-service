<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Events\Auth\PasswordReset;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use App\Models\User;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionOccurredEvent;

final readonly class StagePasswordReset
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $event->user->loadMissing('profile');
        }

        $routingKey = UserRoutingKey::ACTION_OCCURRED;

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
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
