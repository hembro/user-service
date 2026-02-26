<?php

declare(strict_types=1);

namespace App\DTOs\Messages;

use App\Enums\Infrastructure\RequestType;

final readonly class ActionRequestData
{
    public function __construct(
        public RequestType $type,
        public ?string $token = null,
        public ?string $expiresAt = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'expires_at' => $this->expiresAt,
            'token' => $this->token,
        ]);
    }
}
