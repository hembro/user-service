<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Api\V1\Admin\IntegrationEvents\UserRestoredIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserRestored;
use App\Models\OutboxEvent;

final class EnqueueUserRestored
{
    public function handle(UserRestored $event): void
    {
        $payload = UserRestoredIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_RESTORED->value,
            'payload' => $payload,
        ]);
    }
}
