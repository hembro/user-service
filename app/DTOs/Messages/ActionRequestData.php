<?php

declare(strict_types=1);

namespace App\DTOs\Messages;

use App\Enums\Infrastructure\RequestType;

final readonly class ActionRequestData
{
    public function __construct(
        public RequestType $type,
        public string $token,
        public string $expiresAt,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'token' => $this->token,
            'expires_at' => $this->expiresAt,
        ];
    }
}
