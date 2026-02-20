<?php

declare(strict_types=1);

namespace App\Messages\Integration\Auth;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class LoggedOutMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, RequestMetadata $metadata, Systems $originSystem): self
    {
        $messageId = (string) Str::ulid();

        $payload = [
            'event' => RoutingKey::AUTH_LOGGED_OUT->value,
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                ],
                'session' => [
                    'ip_address' => $metadata->ip,
                    'user_agent' => $metadata->userAgent,
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
