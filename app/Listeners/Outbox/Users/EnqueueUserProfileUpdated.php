<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserProfileUpdatedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserProfileUpdated;
use App\Models\OutboxEvent;

final class EnqueueUserProfileUpdated
{
    public function handle(UserProfileUpdated $event): void
    {
        $payload = UserProfileUpdatedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_PROFILE_UPDATED->value,
            'payload' => $payload,
        ]);
    }
}
