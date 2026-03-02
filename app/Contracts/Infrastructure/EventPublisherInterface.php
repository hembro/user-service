<?php

declare(strict_types=1);

namespace App\Contracts\Infrastructure;

interface EventPublisherInterface
{
    public function publish(RoutingKey $routingKey, array $payload): void;
}
