<?php

declare(strict_types=1);

namespace App\DTOs\Messages;

final readonly class Target
{
    public function __construct(
        public string $id,
        public string $type,
        public array $changes = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'changes' => $this->changes,
        ];
    }
}
