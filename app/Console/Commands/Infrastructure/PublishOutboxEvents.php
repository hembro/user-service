<?php

declare(strict_types=1);

namespace App\Console\Commands\Infrastructure;

use App\Enums\Infrastructure\OutboxStatus;
use App\Infrastructure\Amqp\EventPublisher;
use App\Models\OutboxEvent;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PublishOutboxEvents extends Command
{
    protected $signature = 'events:publish';

    protected $description = 'Reads pending events from DB and pushes to RabbitMQ';

    public function __construct(
        private readonly EventPublisher $publisher,
        private readonly DatabaseManager $db
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->db->transaction(
            callback: function () {

                $events = OutboxEvent::query()
                    ->where('status', OutboxStatus::PENDING)
                    ->orderBy('created_at') // FIFO
                    ->limit(100)
                    ->lockForUpdate() // Prevent race condition
                    ->get();

                foreach ($events as $event) {
                    $this->processEvent($event);
                }

            });
    }

    private function processEvent(OutboxEvent $event): void
    {
        try {
            $this->publisher->publish($event->event_type, $event->payload);

            $event->update(['status' => OutboxStatus::PUBLISHED]);
        } catch (Throwable $e) {
            Log::channel('system')->error("Failed to publish outbox event {$event->id}: " . $e->getMessage());
            $event->update([
                'status' => OutboxStatus::FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
