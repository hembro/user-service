<?php

declare(strict_types=1);

namespace App\Infrastructure\Amqp;

use App\Enums\Infrastructure\ExchangeType;
use App\Enums\Infrastructure\RoutingKey;
use Illuminate\Support\Facades\Queue;

final class EventPublisher
{
    /**
     * Publish a raw JSON payload to the Topic Exchange.
     */
    public function publish(RoutingKey $routingKey, array $payload): void
    {
        $connection = Queue::connection('rabbitmq_events');

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
