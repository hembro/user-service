<?php

declare(strict_types=1);

namespace App\Messages\Integration\Auth;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\DTOs\Shared\RequestMetadata;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class EnableTwoFactorRequestedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, Systems $originSystem, RequestMetadata $metadata): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $mesagegeId = (string) Str::ulid();

        $payload = [
            'message_id' => $mesagegeId,
            'event' => RoutingKey::AUTH_TWO_FACTOR_REQUESTED->value,
            'data' => [
                'actor' => [
                    'id' => (string) $user->id,
                    'type' => ActorType::USER->value,
                    'email' => $user->email,
                    'name' => $user->profile?->first_name,
                ],
                'session' => [
                    'ip_address' => $metadata->ip,
                    'user_agent' => $metadata->userAgent,
                ],
            ],
            'meta' => MessageMeta::generate($originSystem),
        ];

        return new self($mesagegeId, $payload);
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
