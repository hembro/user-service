<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Shared;

use Illuminate\Http\Request;

final readonly class RequestMetadata
{
    public function __construct(
        public string $ip,
        public string $userAgent,
        public int $timestamp
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ip: $request->ip() ?? '127.0.0.1',
            userAgent: $request->userAgent() ?? 'unknown',
            timestamp: now()->timestamp,
        );
    }
}
