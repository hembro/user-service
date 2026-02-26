<?php

declare(strict_types=1);

namespace App\DTOs\Messages;

use App\Enums\Infrastructure\ResourceType;

final readonly class Target
{
    public function __construct(
        public string $id,
        public ResourceType $resourceType,
        public array $attributes = [],
        public array $changes = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'resource_type' => $this->resourceType,
            'attributes' => $this->attributes,
            'changes' => $this->changes,
        ];
    }
}
