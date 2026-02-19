<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserPasswordUpdated;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserPasswordUpdatedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $id,
        public string $email,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserPasswordUpdated $event): self
    {
        return new self(
            id: (string) $event->user->id,
            email: $event->user->email,
            originSystem: request()->attributes->get('system')->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_PASSWORD_UPDATED->value,
            'data' => [
                'user' => [
                    'id' => $this->id,
                    'email' => $this->email,
                ],
                'actor' => [
                    'id' => $this->id, // The user acted upon themselves
                    'type' => 'user',
                    'name' => null, // Optional: Keep payload light unless required
                ],
                'origin_system' => $this->originSystem,
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
