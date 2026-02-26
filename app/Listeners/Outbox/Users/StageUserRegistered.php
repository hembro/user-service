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
use App\Events\Users\UserRegistered;
use App\Messages\Integration\Shared\ActionRequestedMessage;
use App\Messages\Integration\Shared\EntityCreatedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;
use Illuminate\Support\Facades\Config;

final readonly class StageUserRegistered
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRegistered $event): void
    {
        $event->user->loadMissing('profile');

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
                'created_at' => $event->user->created_at->toIso8601String(),
            ]
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $this->outbox->publish(
            routingKey: RoutingKey::USER_REGISTERED,
            message: EntityCreatedMessage::make(RoutingKey::USER_REGISTERED, $actor, $target, $meta)
        );

        if ($event->verificationUrl !== null) {

            $actionRequest = new ActionRequestData(
                type: RequestType::DEVICE_VERIFICATION,
                token: $event->verificationUrl,
                expiresAt: now()
                    ->addMinutes((int) Config::get('auth.verification.expire'))
                    ->toIso8601String()
            );

            $this->outbox->publish(
                routingKey: RoutingKey::AUTH_VERIFICATION_REQUESTED,
                message: ActionRequestedMessage::make(RoutingKey::AUTH_VERIFICATION_REQUESTED, $actor, $target, $actionRequest, $meta)
            );
        }
    }
}
