<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserRestored;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserRestoredIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userEmail,
        public string $status,
        public string $adminId,
        public ?string $adminName,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserRestored $event): self
    {
        $event->admin->loadMissing('profile');

        return new self(
            userId: (string) $event->user->id,
            userEmail: $event->user->email,
            status: $event->user->status->value,
            adminId: (string) $event->admin->id,
            adminName: $event->admin->profile?->full_name,
            originSystem: $event->system->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_RESTORED->value,
            'data' => [
                'user' => [
                    'id' => $this->userId,
                    'email' => $this->userEmail,
                    'status' => $this->status,
                ],
                'actor' => [
                    'id' => $this->adminId,
                    'type' => 'admin',
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
