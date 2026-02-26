<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\PasswordReset;
use App\Messages\Integration\Shared\EntityUpdatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use App\Services\Outbox\OutboxPublisher;

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

        $routingKey = RoutingKey::AUTH_PASSWORD_RESET;

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $target = new Target(
            id: (string) $event->user->id,
            resourceType: ResourceType::USER,
            attributes: [
                'name' => $event->user->profile?->first_name ?? $event->user->email,
                'email' => $event->user->email,
            ]
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_PASSWORD_RESET,
            message: EntityUpdatedMessage::make($routingKey, $actor, $target, $meta)
        );
    }
}
