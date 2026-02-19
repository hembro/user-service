<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Api\V1\Admin\IntegrationEvents\UserDeletedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserDeleted;
use App\Models\OutboxEvent;

final class EnqueueUserDeleted
{
    public function handle(UserDeleted $event): void
    {
        $payload = UserDeletedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_DELETED->value,
            'payload' => $payload,
        ]);
    }
}
