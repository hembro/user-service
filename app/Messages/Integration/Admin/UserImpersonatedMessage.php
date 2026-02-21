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

final readonly class UserImpersonatedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, User $impersonator, Systems $originSystem): self
    {
        if (! $impersonator->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_IMPERSONATED->value,
            'data' => [
                'target_user' => [
                    'id' => (string) $user->id,
                    'email' => $user->email,
                ],
                'actor' => [
                    'id' => (string) $impersonator->id,
                    'type' => 'admin',
                    'name' => $impersonator->profile?->full_name,
                ],
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
