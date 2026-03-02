<?php

declare(strict_types=1);

namespace App\Mappers\Integration;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use jeremyaliparo\IntegrationContracts\DTOs\Metadata;

final readonly class SharedIntegrationMapper
{
    public static function extractMetadata(?string $sourceSystem = null): Metadata
    {
        return new Metadata(
            sourceSystem: $sourceSystem ?? Context::get('source_system'),
            sourceService: (string) Config::get('app.name', 'user-service'),
            timestamp: now()->toIso8601String(),
            traceId: (string) Context::get('trace_id', 'unknown-trace-id'),
            ipAddress: (string) Context::get('ip_address'),
            userAgent: (string) Context::get('user_agent'),
            clientType: (string) Context::get('client_type'),
        );
    }
}
