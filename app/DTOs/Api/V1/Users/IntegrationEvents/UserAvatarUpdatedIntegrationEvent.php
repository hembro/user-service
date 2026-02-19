<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserAvatarUpdated;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserAvatarUpdatedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $avatarPath,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserAvatarUpdated $event): self
    {
        $event->user->loadMissing('profile');

        return new self(
            id: (string) $event->user->id,
            email: $event->user->email,
            avatarPath: $event->user->profile?->avatar_path,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_AVATAR_UPDATED->value,
            'data' => [
                'user' => [
                    'id' => $this->id,
                    'email' => $this->email,
                ],
                'profile' => [
                    'avatar_path' => $this->avatarPath,
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
