<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use App\Mappers\Integration\UserIntegrationMapper;
use Illuminate\Support\Facades\Config;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Enums\ActionRequestType;
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
        $metadata = UserIntegrationMapper::extractMetadata($event->system->value);

        $userRegisteredMessage = IntegrationMessage::make(
            eventName: RoutingKey::USER_REGISTERED->value,
            data: new UserRegisteredEvent(
                actor: $actor,
                target: $target,
                occurredAt: $event->user->created_at->toIso8601String(),
                systemContext: $event->systemContext
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: RoutingKey::USER_REGISTERED->value,
            message: $userRegisteredMessage
        );

        if ($event->verificationUrl !== null) {

            $actionRequest = new ActionRequest(
                type: ActionRequestType::DEVICE_VERIFICATION,
                token: $event->verificationUrl,
                expiresAt: now()
                    ->addMinutes((int) Config::get('auth.verification.expire', 60))
                    ->toIso8601String()
            );

            $actionEventData = new ActionRequestedEvent(
                actor: $actor,
                target: $target,
                action: $actionRequest
            );

            $actionMessage = IntegrationMessage::make(
                eventName: RoutingKey::AUTH_VERIFICATION_REQUESTED->value,
                data: $actionEventData,
                metadata: $metadata
            );

            $this->outbox->publish(
                routingKey: RoutingKey::AUTH_VERIFICATION_REQUESTED->value,
                message: $actionMessage
            );
        }
    }
}
