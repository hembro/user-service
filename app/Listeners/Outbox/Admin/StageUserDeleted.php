<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserDeleted;
use App\Messages\Integration\Shared\EntityUpdatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserDeleted
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserDeleted $event): void
    {
        $event->actor->loadMissing('profile');

        $routingKey = RoutingKey::USER_TRASHED;

        $actor = new Actor(
            id: (string) $event->actor->id,
            type: ActorType::USER,
            name: $event->actor->profile?->first_name ?? $event->actor->email,
            email: $event->actor->email
        );

        $target = new Target(
            id: $event->userId,
            type: ResourceType::USER,
            attributes: [
                'name' => $event->userName,
                'email' => $event->userEmail,
            ],
            changes: [
                'deleted_at' => [
                    'old' => null,
                    'new' => now()->toIso8601String(),
                ],
            ]
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: $routingKey,
            message: EntityUpdatedMessage::make($routingKey, $actor, $target, $meta)
        );
    }
}
