<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\PasswordResetRequested;
use App\Messages\Integration\Auth\PasswordResetRequestedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StagePasswordResetRequested
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(PasswordResetRequested $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            RoutingKey::AUTH_PASSWORD_RESET_REQUESTED,
            PasswordResetRequestedMessage::make($event->user, $event->token, $event->system)
        );
    }
}
