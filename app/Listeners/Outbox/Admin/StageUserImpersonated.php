<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserImpersonated;
use App\Messages\Integration\Admin\UserImpersonatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserImpersonated
{
    public function __construct(
        public OutboxPublisher $outbox
    ) {}

    public function handle(UserImpersonated $event): void
    {
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_IMPERSONATED,
            message: UserImpersonatedMessage::make($event->targetUser, $event->actor, $event->system)
        );
    }
}
