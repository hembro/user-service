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
use App\Events\Auth\DeviceVerificationRequested;
use App\Messages\Integration\Shared\ActionRequestedMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;
use Illuminate\Support\Facades\Config;

final readonly class StageDeviceVerificationRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(DeviceVerificationRequested $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::AUTH_DEVICE_VERIFICATION_REQUESTED;

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? 'Unknown',
            email: $event->user->email
        );

        $target = new Target(
            id: (string) $event->user->id,
            type: ResourceType::USER,
            attributes: [
                'name' => $event->user->profile?->first_name ?? 'Unknown',
                'email' => $event->user->email,
            ]
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $actionRequest = new ActionRequestData(
            type: RequestType::DEVICE_VERIFICATION_REQUEST,
            token: $event->otpCode,
            expiresAt: now()
                ->addMinutes((int) Config::get('auth.otp.expire', 10))
                ->toIso8601String()
        );

        $this->outbox->publish(
            RoutingKey::AUTH_DEVICE_VERIFICATION_REQUESTED,
            ActionRequestedMessage::make($routingKey, $actor, $target, $actionRequest, $meta)
        );
    }
}
