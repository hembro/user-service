<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\VerificationLinkRequested;
use App\Messages\Integration\Auth\EmailVerificationRequestedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageVerificationLinkRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(VerificationLinkRequested $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_VERIFICATION_REQUESTED,
            message: EmailVerificationRequestedMessage::make($event->user, $event->verificationUrl, $event->system)
        );
    }
}
