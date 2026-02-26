<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserAvatarUpdated;
use App\Messages\Integration\Shared\EntityUpdatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserAvatarUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserAvatarUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::USER_AVATAR_UPDATED;

        $actor = new Actor(
            id: $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name
        );

        $target = new Target(
            id: $event->user->id,
            type: 'user',
            changes: $event->changes
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: $routingKey,
            message: EntityUpdatedMessage::make($routingKey, $actor, $target, $meta)
        );
    }
}
