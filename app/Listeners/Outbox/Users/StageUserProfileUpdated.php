<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserProfileUpdated;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use jeremyaliparo\IntegrationContracts\DTOs\Metadata;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\ResourceType;

final readonly class StageUserProfileUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserProfileUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::USER_PROFILE_UPDATED;

        $actor = new Actor(
            id: $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $target = new Target(
            id: $event->user->id,
            type: ResourceType::USER,
            attributes: new UserAttributes(
                name: $event->user->profile?->first_name ?? $event->user->email,
                email: $event->user->email,
            )
        );

        // $userUpdatedEventData =

        $metadata = new Metadata(
            sourceSystem: $event->system->value,
            sourceService: Config::string('app.name', 'user-service'),
            timestamp: now()->toIso8601String(),
            traceId: Context::get('trace_id', 'unknown-trace-id'),
            ipAddress: Context::get('ip_address'),
            userAgent: Context::get('user_agent'),
            clientType: Context::get('client_type'),
        );

        // $this->outbox->publish(
        //     routingKey: $routingKey->value,
        //     message: IntegrationMessage::make(
        //         eventName:: $routingKey->value,
        //         data: new User)
        // );
    }
}
