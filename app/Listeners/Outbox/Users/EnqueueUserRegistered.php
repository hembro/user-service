<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserRegisteredIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use App\Models\OutboxEvent;

final class EnqueueUserRegistered
{
    public function handle(UserRegistered $event): void
    {
        $payload = UserRegisteredIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_REGISTERED->value,
            'payload' => $payload,
        ]);
    }
}
