<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Events\Users\UserRegistered;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use Illuminate\Support\Facades\Config;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionRequestType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionRequestedEvent;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserRegisteredEvent;

final readonly class StageUserRegistered
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRegistered $event): void
    {
        $event->user->loadMissing('profile');

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $this->outbox->publish(
            routingKey: UserRoutingKey::USER_REGISTERED->value,
            message: IntegrationMessage::make(
                eventName: UserRoutingKey::USER_REGISTERED->value,
                data: new UserRegisteredEvent(
                    actor: $actor,
                    target: $target,
                    occurredAt: $event->user->created_at->toIso8601String(),
                    systemContext: $event->systemContext
                ),
                metadata: $metadata
            )
        );

        if ($event->verificationUrl !== null) {

            $actionRequest = new ActionRequest(
                type: UserActionRequestType::DEVICE_VERIFICATION,
                token: $event->verificationUrl,
                expiresAt: now()
                    ->addMinutes((int) Config::get('auth.verification.expire', 60))
                    ->toIso8601String()
            );

            $actionMessage = IntegrationMessage::make(
                eventName: UserRoutingKey::ACTION_REQUESTED->value,
                data: new ActionRequestedEvent(
                    actor: $actor,
                    target: $target,
                    action: $actionRequest
                ),
                metadata: $metadata
            );

            $this->outbox->publish(
                routingKey: UserRoutingKey::ACTION_REQUESTED->value,
                message: $actionMessage
            );
        }
    }
}
