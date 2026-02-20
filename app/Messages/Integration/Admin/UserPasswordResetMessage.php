<?php

declare(strict_types=1);

namespace App\Messages\Integration\Admin;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserPasswordResetMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $targetUser, User $actor, Systems $originSystem): self
    {
        if (! $actor->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_PASSWORD_RESET->value,
            'data' => [
                'user' => [
                    'id' => (string) $targetUser->id,
                    'email' => $targetUser->email,
                ],
                'actor' => [
                    'id' => $actor->id,
                    'type' => 'admin',
                    'name' => $actor->profile?->full_name,
                ],
                'origin_system' => $originSystem->value,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'source' => config('app.name'),
                'origin_system' => $originSystem->value,
                'version' => '1.0',
            ],
        ];

        return new self(
            messageId: $messageId,
            payload: $payload
        );
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
