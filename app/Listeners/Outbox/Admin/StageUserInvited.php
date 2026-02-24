<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserInvited;
use App\Messages\Integration\Admin\UserInvitedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserInvited
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserInvited $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_INVITED,
            message: UserInvitedMessage::make($event->targetUser, $event->actor, $event->system)
        );
    }
}
