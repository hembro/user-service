<?php

declare(strict_types=1);

namespace App\DTOs\Messages;

use App\Enums\Infrastructure\ActorType;

final readonly class Actor
{
    public function __construct(
        public string $id,
        public ActorType $type,
        public string $name,
        public ?string $email = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
