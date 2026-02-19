<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserInvited;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserInvitedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userEmail,
        public string $userStatus,
        public string $adminId,
        public ?string $adminName,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserInvited $event): self
    {
        $event->admin->loadMissing('profile');

        return new self(
            userId: (string) $event->user->id,
            userEmail: $event->user->email,
            userStatus: $event->user->status->value,
            adminId: (string) $event->admin->id,
            adminName: $event->admin->profile?->full_name,
            originSystem: $event->system->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_INVITED->value,
            'data' => [
                'user' => [
                    'id' => $this->userId,
                    'email' => $this->userEmail,
                    'status' => $this->userStatus,
                ],
                'actor' => [
                    'id' => $this->adminId,
                    'name' => $this->adminName,
                ],
                'origin_system' => $this->originSystem,
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
