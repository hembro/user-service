<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Shared;

use App\Enums\Infrastructure\RoutingKey;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

final readonly class AuditLogIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $actorId,         // Who did it?
        public string $actorType,       // 'user', 'admin', 'system'
        public string $action,          // 'user.login', 'user.logout'
        public string $resourceId,      // The ID of the thing being changed
        public string $resourceType,    // 'user', 'proposal', 'research'
        public array $oldValues,        // Before change (nullable)
        public array $newValues,        // After change
        public string $originSystem,    // 'pms', 'herdin', 'phrr'
        public string $ipAddress,
        public string $userAgent,
        public string $occurredAt,
    ) {}

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::AUDIT_LOG_CREATED->value,
            'data' => [
                'id' => (string) Str::ulid(),
                'actor' => [
                    'id' => $this->actorId,
                    'type' => $this->actorType,
                ],
                'action' => $this->action,
                'target' => [
                    'id' => $this->resourceId,
                    'type' => $this->resourceType,
                ],
                'changes' => [
                    'before' => $this->oldValues,
                    'after' => $this->newValues,
                ],
                'context' => [
                    'system' => $this->originSystem,
                    'service' => config('app.name'),
                    'ip' => $this->ipAddress,
                    'user_agent' => $this->userAgent,
                ],
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
