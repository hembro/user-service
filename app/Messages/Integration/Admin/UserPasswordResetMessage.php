<?php

declare(strict_types=1);

namespace App\Messages\Integration\Admin;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
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
                    'id' => (string) $actor->id,
                    'type' => 'admin',
                    'name' => $actor->profile?->full_name,
                ],
                'origin_system' => $originSystem->value,
            ],
            'meta' => MessageMeta::generate($originSystem),
        ];

        return new self($messageId, $payload);
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
