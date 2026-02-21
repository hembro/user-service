<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserEmailChanged;
use App\Messages\Integration\Admin\UserEmailChangedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserEmailChanged
{
    public function __construct(
        public OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChanged $event): void
    {
        $event->targetUser->loadMissing('profile');
        $event->actor->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::ADMIN_USER_EMAIL_CHANGED,
            message: UserEmailChangedMessage::make($event->targetUser, $event->actor, $event->system)
        );
    }
}
