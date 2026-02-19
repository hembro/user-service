<?php

declare(strict_types=1);

namespace App\Listeners\Outbox\Auth;

use App\DTOs\Api\V1\Users\IntegrationEvents\UserPasswordResetIntegrationEvent;
use App\Enums\Infrastructure\RoutingKey;
use App\Models\OutboxEvent;
use Illuminate\Auth\Events\PasswordReset;

final class EnqueuePasswordReset
{
    public function handle(PasswordReset $event): void
    {
        $payload = UserPasswordResetIntegrationEvent::fromUserReset($event)->toArray();

        OutboxEvent::create([
            'event_type' => RoutingKey::USER_PASSWORD_RESET->value,
            'payload' => $payload,
        ]);
    }
}
