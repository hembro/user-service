<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserEmailChanged;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserEmailChangedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $id,
        public string $oldEmail,
        public string $newEmail,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserEmailChanged $event): self
    {
        return new self(
            id: (string) $event->user->id,
            oldEmail: $event->oldEmail,
            newEmail: $event->user->email,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_EMAIL_CHANGED->value,
            'data' => [
                'user' => [
                    'id' => $this->id,
                    'changes' => [
                        'old_email' => $this->oldEmail,
                        'new_email' => $this->newEmail,
                    ],
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
