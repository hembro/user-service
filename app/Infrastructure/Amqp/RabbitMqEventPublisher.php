<?php

declare(strict_types=1);

namespace App\Infrastructure\Amqp;

use App\Contracts\Infrastructure\EventPublisherInterface;
use App\Enums\Infrastructure\ExchangeType;
use App\Enums\Infrastructure\RoutingKey;
use Illuminate\Contracts\Queue\Factory as QueueFactory;

final readonly class RabbitMqEventPublisher implements EventPublisherInterface
{
    public function __construct(
        private QueueFactory $queue
    ) {}

    public function publish(RoutingKey $routingKey, array $payload): void
    {
        $connection = $this->queue->connection('rabbitmq_events');

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        $connection->pushRaw(
            payload: $jsonPayload,
            queue: $routingKey->value,
            options: [
                'exchange' => ExchangeType::TOPIC->value,
            ]
        );
    }
}
