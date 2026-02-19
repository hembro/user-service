<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Users;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserAvatarUpdatedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserAvatarUpdated;
use App\Models\OutboxEvent;

final class EnqueueUserAvatarUpdated
{
    public function handle(UserAvatarUpdated $event): void
    {
        $payload = UserAvatarUpdatedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_AVATAR_UPDATED->value,
            'payload' => $payload,
        ]);
    }
}
