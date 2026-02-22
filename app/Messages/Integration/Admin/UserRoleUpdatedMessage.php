<?php

declare(strict_types=1);

namespace App\Messages\Integration\Admin;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\ActorType;
use App\Enums\Infrastructure\RoutingKey;
use App\Enums\Systems;
use App\Messages\Integration\Shared\MessageMeta;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class UserRoleUpdatedMessage implements IntegrationMessageInterface
{
    private function __construct(
        private string $messageId,
        private array $payload
    ) {}

    public static function make(User $targetUser, User $actor, array $oldRoles, array $newRoles, Systems $originSystem): self
    {
        $messageId = (string) Str::ulid();

        $payload = [
            'message_id' => $messageId,
            'event' => RoutingKey::USER_ROLE_UPDATED->value,
            'data' => [
                'target_user' => [
                    'id' => (string) $targetUser->id,
                    'email' => $targetUser->email,
                    'old_roles' => $oldRoles,
                    'new_roles' => $newRoles,
                ],
                'actor' => [
                    'id' => (string) $actor->id,
                    'type' => ActorType::USER->value,
                    'name' => $actor->profile?->full_name,
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
