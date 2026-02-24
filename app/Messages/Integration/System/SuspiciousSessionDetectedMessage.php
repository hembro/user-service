<?php

declare(strict_types=1);

namespace App\Messages\Integration\System;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\DTOs\Shared\RequestMetadata;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

final readonly class SuspiciousSessionDetectedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $user, Systems $originSystem, RequestMetadata $metadata): self
    {
        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::SYSTEM_SUSPICIOUS_SESSION->value,
            'data' => [
                'actor' => [
                    'id' => (string) $user->id,
                    'type' => ActorType::USER->value,
                    'email' => $user->email,
                ],
                'session' => [
                    'ip' => $metadata->ip,
                    'user_agent' => $metadata->userAgent,
                ],
            ],
            'meta' => MessageMeta::generate($originSystem, Carbon::createFromTimestamp($metadata->timestamp)->toIso8601String()),
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
