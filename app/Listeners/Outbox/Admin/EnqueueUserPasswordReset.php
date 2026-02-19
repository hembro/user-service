<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Admin;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserPasswordResetIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserPasswordReset;
use App\Models\OutboxEvent;

final class EnqueueUserPasswordReset
{
    public function handle(UserPasswordReset $event): void
    {
        $payload = UserPasswordResetIntegrationEvent::fromAdminReset($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_PASSWORD_RESET->value,
            'payload' => $payload,
        ]);
    }
}
