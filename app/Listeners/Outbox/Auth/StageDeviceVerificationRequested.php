<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Events\Auth\DeviceVerificationRequested;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use Illuminate\Support\Facades\Config;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Commons\ActionRequest;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionRequestType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionRequestedEvent;

final readonly class StageDeviceVerificationRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(DeviceVerificationRequested $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = UserRoutingKey::ACTION_REQUESTED;

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new ActionRequestedEvent(
                actor: $actor,
                target: $target,
                action: new ActionRequest(
                    type: UserActionRequestType::DEVICE_VERIFICATION,
                    token: $event->otpCode,
                    expiresAt: now()
                        ->addMinutes((int) Config::get('auth.otp.expire', 10))
                        ->toIso8601String()
                )
            ),
            metadata: $metadata
        );

        $this->outbox->publish(
            routingKey: $routingKey->value,
            message: $message
        );
    }
}
