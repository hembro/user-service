<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserRestored;
use App\Messages\Integration\Admin\UserRestoredMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserRestored
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserRestored $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_RESTORED,
            message: UserRestoredMessage::make($event->targetUser, $event->actor, $event->system)
        );
    }
}
