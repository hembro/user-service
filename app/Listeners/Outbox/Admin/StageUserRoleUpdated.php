<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserRoleUpdated;
use App\Messages\Integration\Admin\UserRoleUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserRoleUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRoleUpdated $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_ROLE_UPDATED,
            message: UserRoleUpdatedMessage::make($event->targetUser, $event->actor, $event->oldRoles, $event->newRoles, $event->system)
        );
    }
}
