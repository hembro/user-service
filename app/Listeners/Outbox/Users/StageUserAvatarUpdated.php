<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserAvatarUpdated;
use App\Messages\Integration\Users\UserAvatarUpdatedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserAvatarUpdated
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserAvatarUpdated $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_AVATAR_UPDATED,
            message: UserAvatarUpdatedMessage::make($event->user, $event->system)
        );
    }
}
