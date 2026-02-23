<?php

declare(strict_types=1);

namespace App\Jobs\Outbox;

use App\Enums\Infrastructure\OutboxStatus;
use App\Infrastructure\Amqp\EventPublisher;
use App\Models\OutboxEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PublishOutboxEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 5;

    public array $backoff = [2, 10, 30, 60];

    /**
     * Delete the job if the model is missing (e.g., a DB purge occurred).
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly string $outboxEventId
    ) {
        $this->queue = 'outbox';
    }

    public function handle(EventPublisher $publisher): void
    {
        $event = OutboxEvent::query()
            ->where('id', $this->outboxEventId)
            ->where('status', OutboxStatus::PENDING)
            ->first();

        if (! $event) {
            $this->delete();

            return;
        }

        try {
            $publisher->publish($event->event_type, $event->payload);
            $event->update(['status' => OutboxStatus::PUBLISHED]);
        } catch (Throwable $e) {
            $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('system')->critical(
            message: 'Outbox event totally failed after all retries.',
            context: [
                'event_id' => $this->outboxEventId,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        OutboxEvent::query()
            ->where('id', $this->outboxEventId)
            ->update([
                'status' => OutboxStatus::FAILED,
                'error' => $exception->getMessage(),
            ]);
    }
}
