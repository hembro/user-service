<?php

declare(strict_types=1);

namespace App\Messages\Integration\Auth;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class EmailVerificationRequestedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, string $verificationUrl, Systems $originSystem): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::AUTH_VERIFICATION_REQUESTED->value,
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                    'email' => $user->email,
                    'first_name' => $user->profile?->first_name,
                ],
                'verification_url' => $verificationUrl,
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
