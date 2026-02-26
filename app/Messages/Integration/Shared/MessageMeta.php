<?php

declare(strict_types=1);

namespace App\Messages\Integration\Shared;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;

final readonly class MessageMeta
{
    public function __construct(
        public string $version,
        public string $source,
        public string $timestamp,
        public Systems $originSystem,
        public string $ipAddress,
        public string $userAgent,
    ) {}

    public static function generate(Systems $originSystem, RequestMetadata $metadata): self
    {
        return new self(
            version: '1.0',
            source: config('app.name'),
            timestamp: now()->toIso8601String(),
            originSystem: $originSystem,
            ipAddress: $metadata->ip,
            userAgent: $metadata->userAgent,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source,
            'version' => $this->version,
            'timestamp' => $this->timestamp,
            'origin_system' => $this->originSystem->value,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ]);
    }
}
