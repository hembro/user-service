<?php

declare(strict_types=1);

namespace App\Messages\Integration\Users;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserRegisteredMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, Systems $originSystem): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_REGISTERED->value,
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                    'email' => $user->email,
                    'status' => $user->status->value,
                    'full_name' => $user->profile?->full_name,
                    'title' => $user->profile?->title?->value,
                    'first_name' => $user->profile?->first_name,
                    'last_name' => $user->profile?->last_name,
                    'suffix' => $user->profile?->suffix?->value,
                    'mobile_number' => $user->profile?->mobile_number,
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                ],
            ],
            'meta' => [
                'timestamp' => $user->created_at->toIso8601String(),
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
