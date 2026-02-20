<?php

declare(strict_types=1);

namespace App\Messages\Integration\Users;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserEmailChangeRequestedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, string $token, string $newEmail, Systems $originSystem): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_EMAIL_CHANGE_REQUESTED->value,
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                    'token' => $token,
                    'new_email' => $newEmail,
                    'name' => $user->profile?->first_name ?? 'User',
                ],
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'source' => config('app.name'),
                'origin_system' => $originSystem->value,
                'version' => '1.0',
            ],
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
