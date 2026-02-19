<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Api\V1\Auth\IntegrationEvents\UserLoggedOutIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserLoggedOut;
use App\Models\OutboxEvent;

final class EnqueueUserLoggedOut
{
    public function handle(UserLoggedOut $event): void
    {
        $payload = UserLoggedOutIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_LOGGED_OUT->value,
            'payload' => $payload,
        ]);
    }
}
