<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserProfileUpdated;
use App\Messages\Integration\Users\UserProfileUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserProfileUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserProfileUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_PROFILE_UPDATED,
            message: UserProfileUpdatedMessage::make($event->user, $event->changes, $event->system)
        );
    }
}
