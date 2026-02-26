<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Messages\Actor;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\TwoFactorDisabled;
use App\Messages\Integration\Shared\ActionOccurredMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageTwoFactorDisabled
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(TwoFactorDisabled $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::AUTH_TWO_FACTOR_DISABLED;

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_TWO_FACTOR_DISABLED,
            message: ActionOccurredMessage::make($routingKey, $actor, $meta)
        );
    }
}
