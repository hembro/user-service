<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Events\Users\UserEmailChangeRequested;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use Illuminate\Support\Facades\Config;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionRequestType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionRequestedEvent;

final readonly class StageUserEmailChangeRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChangeRequested $event): void
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
                    type: UserActionRequestType::EMAIL_CHANGE,
                    token: $event->token,
                    expiresAt: now()
                        ->addMinutes((int) Config::get('auth.verification.expire', 10))
                        ->toIso8601String()
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
