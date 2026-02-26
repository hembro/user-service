<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Messages\Actor;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserLoggedOut;
use App\Messages\Integration\Shared\ActionOccurredMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserLoggedOut
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserLoggedOut $event): void
    {
        $routingKey = RoutingKey::AUTH_LOGGED_OUT;

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: $routingKey,
            message: ActionOccurredMessage::make($routingKey, $actor, $meta)
        );
    }
}
