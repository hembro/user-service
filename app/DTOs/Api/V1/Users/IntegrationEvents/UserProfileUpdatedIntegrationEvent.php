<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserProfileUpdated;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserProfileUpdatedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $id,
        public string $email,
        public array $changes,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserProfileUpdated $event): self
    {
        return new self(
            id: (string) $event->user->id,
            email: $event->user->email,
            changes: $event->changes,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_PROFILE_UPDATED->value,
            'data' => [
                'user' => [
                    'id' => $this->id,
                    'email' => $this->email,
                ],
                'profile' => [
                    'changes' => $this->changes,
                ],
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
