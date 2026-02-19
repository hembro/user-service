<?php

declare(strict_types=1);

namespace App\Listeners\Outbox;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserEmailChangedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserEmailChanged;
use App\Models\OutboxEvent;

final class EnqueueUserEmailChanged
{
    public function handle(UserEmailChanged $event): void
    {
        $payload = UserEmailChangedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_EMAIL_CHANGED->value,
            'payload' => $payload,
        ]);
    }
}
