<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserDeleted;
use App\Messages\Integration\Admin\UserDeletedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserDeleted
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserDeleted $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_DELETED,
            message: UserDeletedMessage::make($event->userId, $event->actor, $event->system)
        );
    }
}
