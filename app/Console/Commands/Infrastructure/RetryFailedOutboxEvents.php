<?php

declare(strict_types=1);

namespace App\Console\Commands\Infrastructure;

use App\Enums\Infrastructure\OutboxStatus;
use App\Jobs\Outbox\PublishOutboxEventJob;
use App\Models\OutboxEvent;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

final class RetryFailedOutboxEvents extends Command
{
    protected $signature = 'outbox:retry {--all : Retry all failed events} {id? : The ID of a specific failed event}';

    protected $description = 'Retry failed outbox events by pushing them back to the pending queue.';

    public function __construct(
        private readonly Dispatcher $bus
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $query = OutboxEvent::query()->where('status', OutboxStatus::FAILED);

        if ($this->argument('id')) {
            $query->where('id', $this->argument('id'));
        } elseif (! $this->option('all')) {
            $this->warn('Please provide an event ID or use the --all flag.');

            return;
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No failed outbox events found.');

            return;
        }

        $events = $query->get();

        $this->info("Found {$events->count()} failed events. Re-queueing...");

        $query->chunkById(1000, function ($events) {
            foreach ($events as $event) {
                $event->update([
                    'status' => OutboxStatus::PENDING,
                    'error' => null,
                ]);

                $this->bus->dispatch(
                    new PublishOutboxEventJob($event->id)
                );
            }
        });

        $this->info('Successfully re-queued all selected events.');
    }
}
