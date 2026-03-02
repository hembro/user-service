<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\System;

use App\Events\Auth\SuspiciousSessionDetected;
use App\Mappers\Integration\SharedIntegrationMapper;
use App\Mappers\Integration\UserIntegrationMapper;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationCore\Messages\IntegrationMessage;
use jeremyaliparo\IntegrationCore\Publishing\OutboxPublisher;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserActionType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;
use jeremyaliparo\IntegrationSchemas\Events\System\ActionOccurredEvent;

final readonly class StageSuspiciousSessionDetected
{
    public function __construct(
        private OutboxPublisher $outbox,
        private DatabaseManager $db
    ) {}

    public function handle(SuspiciousSessionDetected $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = UserRoutingKey::ACTION_OCCURRED;

        $actor = UserIntegrationMapper::toActor($event->user);
        $target = UserIntegrationMapper::toTarget($event->user);
        $metadata = SharedIntegrationMapper::extractMetadata($event->system->value);

        $message = IntegrationMessage::make(
            eventName: $routingKey->value,
            data: new ActionOccurredEvent(
                actor: $actor,
                type: UserActionType::SUSPICIOUS_SESSION,
                target: $target,
                context: [
                    'reason' => $event->reason,
                ]
            ),
            metadata: $metadata
        );

        $this->db->transaction(
            callback: fn () => $this->outbox->publish(
                routingKey: $routingKey->value,
                message: $message
            )
        );
    }
}
