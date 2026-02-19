<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserDeleted;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserDeletedIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $adminId,
        public ?string $adminName,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserDeleted $event): self
    {
        $event->admin->loadMissing('profile');

        return new self(
            userId: $event->userId,
            adminId: (string) $event->admin->id,
            adminName: $event->admin->profile?->full_name,
            originSystem: request()->attributes->get('system')->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_DELETED->value,
            'data' => [
                'user' => [
                    'id' => $this->userId,
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
