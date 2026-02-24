<?php

declare(strict_types=1);

namespace App\Services\Outbox;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\OutboxStatus;
use App\Enums\Infrastructure\RoutingKey;
use App\Jobs\Outbox\PublishOutboxEventJob;
use App\Models\OutboxEvent;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\DatabaseManager;
use LogicException;

final readonly class OutboxPublisher
{
    public function __construct(
        private DatabaseManager $db,
        private Dispatcher $bus
    ) {}

    /**
     * Publishes an event to the outbox.
     * MUST be called inside an active database transaction.
     */
    public function publish(RoutingKey $routingKey, IntegrationMessageInterface $message): void
    {
        if ($this->db->transactionLevel() === 0) {
            throw new LogicException('OutboxPublisher::publish MUST be called inside an active database transaction to ensure distributed consistency.');
        }

        $outbox = OutboxEvent::create([
            'id' => $message->getMessageId(),
            'event_type' => $routingKey->value,
            'payload' => $message->toPayload(),
            'status' => OutboxStatus::PENDING,
        ]);

        // Do the job immediately after the parent transaction commits successfully
        $this->db->afterCommit(
            fn () => $this->bus->dispatch(
                new PublishOutboxEventJob($outbox->id)
            )
        );
    }
}
