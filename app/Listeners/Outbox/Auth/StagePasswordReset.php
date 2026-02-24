<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\PasswordReset;
use App\Messages\Integration\Auth\PasswordResetMessage;
use App\Models\User;
use App\Services\Outbox\OutboxPublisher;

final readonly class StagePasswordReset
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $event->user->loadMissing('profile');
        }

        $this->outbox->publish(
            routingKey: RoutingKey::AUTH_PASSWORD_RESET,
            message: PasswordResetMessage::make($event->user, $event->system)
        );
    }
}
