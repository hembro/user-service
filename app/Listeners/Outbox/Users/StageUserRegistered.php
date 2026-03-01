<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use jeremyaliparo\IntegrationContracts\DTOs\Metadata;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\ActionRequestType;
use jeremyaliparo\IntegrationSchemas\Enums\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\ResourceType;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionRequestedEvent;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserCreatedEvent;

final readonly class StageUserRegistered
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRegistered $event): void
    {
        $event->user->loadMissing('profile');

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $target = new Target(
            id: (string) $event->user->id,
            type: ResourceType::USER,
            attributes: UserAttributes::fromArray([
                'name' => $event->user->profile->first_name ?? $event->user->email,
                'email' => $event->user->email,
            ])
        );

        $userCreatedEventData = new UserCreatedEvent($actor, $target);

        $metadata = new Metadata(
            sourceSystem: $event->system->value,
            sourceService: Config::get('app.name', 'user-service'),
            timestamp: now()->toIso8601String(),
            traceId: Context::get('trace_id', 'unknown-trace-id'),
            ipAddress: Context::get('ip_address'),
            userAgent: Context::get('user_agent'),
            clientType: Context::get('client_type'),
        );

        $userRegisteredMessage = IntegrationMessage::make(
            eventName: RoutingKey::USER_REGISTERED->value,
            data: $userCreatedEventData,
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

            $actionEvent = new ActionRequestedEvent(
                actor: $actor,
                target: $target,
                action: $actionRequest
            );

            $actionMessage = IntegrationMessage::make(
                eventName: RoutingKey::AUTH_VERIFICATION_REQUESTED->value,
                data: $actionEvent,
                metadata: $metadata
            );

            $this->outbox->publish(
                routingKey: RoutingKey::AUTH_VERIFICATION_REQUESTED->value,
                message: $actionMessage
            );
        }
    }
}
