<?php

declare(strict_types=1);

namespace App\Contracts\Infrastructure;

use App\Enums\Infrastructure\RoutingKey;

interface EventPublisherInterface
{
    public function publish(RoutingKey $routingKey, array $payload): void;
}
