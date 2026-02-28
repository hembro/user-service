<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserRoleUpdated;
use App\Messages\Integration\Shared\EntityUpdatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserRoleUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRoleUpdated $event): void
    {
        $event->actor->loadMissing('profile');
        $event->targetUser->loadMissing('profile');

        $routingKey = RoutingKey::USER_ROLE_UPDATED;

        $actor = new Actor(
            id: (string) $event->actor->id,
            type: ActorType::USER,
            name: $event->actor->profile?->first_name ?? $event->actor->email,
            email: $event->actor->email
        );

        $target = new Target(
            id: (string) $event->targetUser->id,
            type: ResourceType::USER,
            attributes: [
                'name' => $event->targetUser->profile?->first_name ?? $event->targetUser->email,
                'email' => $event->targetUser->email,
            ],
            changes: [
                'roles' => [
                    'old' => $event->oldRoles,
                    'new' => $event->newRoles,
                ],
            ]
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: RoutingKey::USER_ROLE_UPDATED,
            message: EntityUpdatedMessage::make($routingKey, $actor, $target, $meta)
        );
    }
}
