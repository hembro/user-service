<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserPasswordUpdatedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserPasswordUpdated;
use App\Models\OutboxEvent;

final class EnqueueUserPasswordUpdated
{
    public function handle(UserPasswordUpdated $event): void
    {
        $payload = UserPasswordUpdatedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_PASSWORD_UPDATED->value,
            'payload' => $payload,
        ]);
    }
}
