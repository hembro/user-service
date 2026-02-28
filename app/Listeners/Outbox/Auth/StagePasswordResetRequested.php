<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Messages\ActionRequestData;
use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RequestType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\PasswordResetRequested;
use App\Messages\Integration\Shared\ActionRequestedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;
use Illuminate\Support\Facades\Config;

final readonly class StagePasswordResetRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(PasswordResetRequested $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::AUTH_PASSWORD_RESET_REQUESTED;

        $actor = new Actor(
            id: 'anonymous',
            type: ActorType::GUEST,
            name: 'Unauthenticated Guest',
            email: null
        );

        $target = new Target(
            id: (string) $event->user->id,
            type: ResourceType::USER,
            attributes: [
                'name' => $event->user->profile?->first_name ?? $event->user->email,
                'email' => $event->user->email,
            ]
        );

        $actionRequest = new ActionRequestData(
            type: RequestType::PASSWORD_RESET_REQUEST,
            token: $event->token,
            expiresAt: now()
                ->addMinutes((int) Config::get('auth.passwords.users.expire', 60))
                ->toIso8601String()
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            RoutingKey::AUTH_PASSWORD_RESET_REQUESTED,
            ActionRequestedMessage::make($routingKey, $actor, $target, $actionRequest, $meta)
        );
    }
}
