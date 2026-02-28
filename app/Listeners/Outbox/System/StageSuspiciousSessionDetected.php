<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\System;

use App\DTOs\Messages\Actor;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\SuspiciousSessionDetected;
use App\Messages\Integration\Shared\ActionOccurredMessage;
use App\Messages\Integration\Shared\MessageMeta;
use App\Services\Outbox\OutboxPublisher;
use Illuminate\Database\DatabaseManager;

final readonly class StageSuspiciousSessionDetected
{
    public function __construct(
        private OutboxPublisher $outbox,
        private DatabaseManager $db
    ) {}

    public function handle(SuspiciousSessionDetected $event): void
    {
        $event->user->loadMissing('profile');

        $routingKey = RoutingKey::SYSTEM_SUSPICIOUS_SESSION;

        $actor = new Actor(
            id: (string) $event->user->id,
            type: ActorType::USER,
            name: $event->user->profile?->first_name ?? $event->user->email,
            email: $event->user->email
        );

        $meta = MessageMeta::generate($event->system, $event->metadata);

        $context = [
            'reason' => $event->reason,
        ];

        $message = ActionOccurredMessage::make($routingKey, $actor, $meta, context: $context);

        $this->db->transaction(
            callback: fn () => $this->outbox->publish(
                routingKey: $routingKey,
                message: $message
            )
        );
    }
}
