<?php

declare(strict_types=1);

namespace App\Listeners\Infrastructure\Outbox;

use App\DTOs\Api\V1\IntegrationEvents\UserRegisteredIntegrationEvent;
use App\Enums\Infrastructure\OutboxStatus;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use App\Models\OutboxEvent;

final class CreateUserRegisteredEntry
{
    public function handle(UserRegistered $event): void
    {
        $payload = UserRegisteredIntegrationEvent::fromModel($event->user)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_REGISTERED->value,
            'payload' => $payload,
            'status' => OutboxStatus::PENDING,
        ]);
    }
}
