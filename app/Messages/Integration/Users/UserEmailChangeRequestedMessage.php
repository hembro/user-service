<?php

declare(strict_types=1);

namespace App\Messages\Integration\Users;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\DTOs\Shared\RequestMetadata;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserEmailChangeRequestedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, string $token, string $newEmail, Systems $originSystem, RequestMetadata $metadata): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_EMAIL_CHANGE_REQUESTED->value,
            'data' => [
                'actor' => [
                    'id' => (string) $user->id,
                    'new_email' => $newEmail,
                    'name' => $user->profile?->first_name ?? 'User',
                ],
                'email_change_token' => $token,
                'session' => [
                    'ip_address' => $metadata->ip,
                    'user_agent' => $metadata->userAgent,
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
