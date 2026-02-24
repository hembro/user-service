<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserEmailChanged;
use App\Messages\Integration\Users\UserEmailChangedMessage;
use App\Services\Outbox\OutboxPublisher;

final readonly class StageUserEmailChanged
{
    public function __construct(
        private OutboxPublisher $outbox
    ) {}

    public function handle(UserEmailChanged $event): void
    {
        $event->user->loadMissing('profile');

        $this->outbox->publish(
            routingKey: RoutingKey::USER_EMAIL_CHANGED,
            message: UserEmailChangedMessage::make($event->user, $event->oldEmail, $event->system)
        );
    }
}
