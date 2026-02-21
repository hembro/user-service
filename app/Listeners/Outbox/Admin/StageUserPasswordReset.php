<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserPasswordReset;
use App\Messages\Integration\Admin\UserPasswordResetMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserPasswordReset
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserPasswordReset $event): void
    {
        $event->actor->loadMissing('profile');
        $event->targetUser->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_PASSWORD_RESET,
            message: UserPasswordResetMessage::make($event->targetUser, $event->actor, $event->system)
        );
    }
}
