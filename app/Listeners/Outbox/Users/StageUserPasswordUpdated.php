<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserPasswordUpdated;
use App\Messages\Integration\USers\UserPasswordUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserPasswordUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserPasswordUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_PASSWORD_UPDATED,
            message: UserPasswordUpdatedMessage::make($event->user, $event->system)
        );
    }
}
