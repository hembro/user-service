<?php

declare(strict_types=1);

namespace App\Console\Commands\Infrastructure;

use App\Enums\Infrastructure\OutboxStatus;
use App\Enums\Infrastructure\RoutingKey;
use App\Infrastructure\Amqp\EventPublisher;
use App\Models\OutboxEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class PublishOutboxEvents extends Command
{
    protected $signature = 'events:publish';

    protected $description = 'Reads pending events from DB and pushes to RabbitMQ';

    public function __construct(
        private readonly EventPublisher $publisher
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        // Process in chunks to avoid memory leaks
        // Order by oldest first (FIFO)
        OutboxEvent::query()
            ->where('status', OutboxStatus::PENDING)
            ->orderBy('created_at')
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    $this->processEvent($event);
                }
            });
    }

    private function processEvent(OutboxEvent $event): void
    {
        try {
            $routingKey = RoutingKey::tryFrom($event->event_type);

            if (! $routingKey) {
                throw new RuntimeException("Invalid routing key: {$event->event_type}");
            }

            $this->publisher->publish($routingKey, $event->payload);

            $event->update(['status' => OutboxStatus::PUBLISHED]);
        } catch (Throwable $e) {
            Log::channel('system')->error("Failed to publish outbox event {$event->id}: " . $e->getMessage());

            $event->updateQuietly([
                'status' => OutboxStatus::FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
