<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserAvatarUpdated;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserAvatarUpdatedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userEmail,
        public ?string $avatarPath,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserAvatarUpdated $event): self
    {
        $event->user->loadMissing('profile');

        return new self(
            userId: (string) $event->user->id,
            userEmail: $event->user->email,
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
                    'id' => $this->userId,
                    'email' => $this->userEmail,
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
