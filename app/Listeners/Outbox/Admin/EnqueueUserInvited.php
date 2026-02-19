<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Api\V1\Admin\IntegrationEvents\UserInvitedIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserInvited;
use App\Models\OutboxEvent;

final class EnqueueUserInvited
{
    public function handle(UserInvited $event): void
    {
        $payload = UserInvitedIntegrationEvent::fromDomainEvent($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_INVITED->value,
            'payload' => $payload,
        ]);
    }
}
