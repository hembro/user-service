<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Messages\ActionRequestData;
use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RequestType;
use App\Enums\Infrastructure\ResourceType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserEmailChangeRequested;
use App\Messages\Integration\Shared\ActionRequestedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;
use Illuminate\Support\Facades\Config;

final readonly class StageUserEmailChangeRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChangeRequested $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::USER_EMAIL_CHANGE_REQUESTED;

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
            changes: [
                'email' => [
                    'old' => $event->user->email,
                    'new' => $event->newEmail,
                ],
            ]
        );

        $actionRequest = new ActionRequestData(
            type: RequestType::EMAIL_CHANGE,
            token: $event->token,
            expiresAt: now()
                ->addMinutes((int) Config::get('auth.verification.expire'))
                ->toIso8601String()
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: $routingKey,
            message: ActionRequestedMessage::make($routingKey, $actor, $target, $actionRequest, $meta)
        );
    }
}
