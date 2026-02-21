<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\DeviceVerificationRequested;
use App\Messages\Integration\Auth\DeviceVerificationRequestedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageDeviceVerificationRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(DeviceVerificationRequested $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            RoutingKey::AUTH_DEVICE_VERIFICATION_REQUESTED,
            DeviceVerificationRequestedMessage::make($event->user, $event->otpCode, $event->system, $event->metadata)
        );
    }
}
