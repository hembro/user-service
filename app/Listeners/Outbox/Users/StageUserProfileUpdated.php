<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserProfileUpdated;
use App\Messages\Integration\Shared\EntityUpdatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

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
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? 'Unknown',
            email: $event->user->email
        );

        $target = new Target(
            id: (string) $event->user->id,
            resourceType: ResourceType::USER,
            attributes: [
                'name' => $event->user->profile?->first_name ?? 'Unknown',
                'email' => $event->user->email,
            ],
            changes: $event->changes
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: $routingKey,
            message: EntityUpdatedMessage::make($routingKey, $actor, $target, $meta)
        );
    }
}
