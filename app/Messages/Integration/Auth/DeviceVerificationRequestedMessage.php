<?php

declare(strict_types=1);

namespace App\Messages\Integration\Auth;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class DeviceVerificationRequestedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, string $otpCode, Systems $originSystem, RequestMetadata $metadata): self
    {
        if (! $user->relationLoaded('profile')) {
            throw new InvalidArgumentException('User profile must be eagerly loaded.');
        }

        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::AUTH_DEVICE_VERIFICATION_REQUESTED->value,
            'data' => [
                'actor' => [
                    'id' => (string) $user->id,
                    'email' => $user->email,
                    'first_name' => $user->profile?->first_name,
                ],
                'verification' => [
                    'otp_code' => $otpCode,
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
