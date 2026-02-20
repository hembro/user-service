<?php

declare(strict_types=1);

namespace App\Messages\Integration\Admin;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserDeletedMessage implements IntegrationMessageInterface
{
    private function __construct(
        public string $messageId,
        public array $payload
    ) {}

    public static function make(string $userId, User $actor, Systems $originSystem): self
    {
        if (! $actor->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_DELETED->value,
            'data' => [
                'user' => [
                    'id' => $userId,
                ],
                'actor' => [
                    'id' => (string) $actor->id,
                    'type' => 'admin',
                    'name' => $actor->profile?->full_name,
                ],
                'origin_system' => $originSystem->value,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];

        return new self(
            messageId: $messageId,
            payload: $payload
        );
    }

    public function getEventId(): string
    {
        return $this->messageId;
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
