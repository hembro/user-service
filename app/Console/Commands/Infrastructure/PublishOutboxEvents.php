<?php

declare(strict_types=1);

namespace App\Console\Commands\Infrastructure;

use App\Enums\Infrastructure\OutboxStatus;
use App\Jobs\Outbox\PublishOutboxEventJob;
use App\Models\OutboxEvent;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Throwable;

final class PublishOutboxEvents extends Command
{
    protected $signature = 'events:publish';

    protected $description = 'Reads pending events from DB and pushes to RabbitMQ';

    public function __construct(
        private readonly Dispatcher $bus
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $query = OutboxEvent::query()
            ->where('status', OutboxStatus::PENDING)
            ->where('created_at', '<=', now()->subMinutes(2))
            ->orderBy('created_at');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No stuck outbox events found.');

            return;
        }

        $this->info("Found {$count} stuck outbox events. Processing synchronously...");

        $query->chunkById(1000, function ($events) {
            foreach ($events as $event) {
                try {
                    $this->bus->dispatchSync(
                        new PublishOutboxEventJob($event->id)
                    );
                } catch (Throwable $exception) {
                    $this->error("Failed to publish event [{$event->id}]: {$exception->getMessage()}");
                }
            }
        });
    }
}
