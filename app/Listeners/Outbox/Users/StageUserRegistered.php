<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use App\Messages\Integration\Auth\EmailVerificationRequestedMessage;
use App\Messages\Integration\Users\UserRegisteredMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserRegistered
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRegistered $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_REGISTERED,
            message: UserRegisteredMessage::make($event->user, $event->system)
        );

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_VERIFICATION_REQUESTED,
            message: EmailVerificationRequestedMessage::make($event->user, $event->verificationUrl, $event->system)
        );
    }
}
