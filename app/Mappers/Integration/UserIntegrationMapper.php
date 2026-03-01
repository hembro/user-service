<?php

declare(strict_types=1);

namespace App\Mappers\Integration;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use jeremyaliparo\IntegrationContracts\DTOs\Metadata;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\ResourceType;

final class UserIntegrationMapper
{
    public static function toActor(User $user): Actor
    {
        return new Actor(
            id: (string) $user->id,
            type: ActorType::USER,
            name: $user->profile->first_name ?? $user->email,
            email: $user->email
        );
    }

    public static function toTarget(User $user): Target
    {
        $attributes = new UserAttributes(
            email: $user->email,
            name: $user->profile->first_name ?? $user->email,
            status: $user->status,
            mobileNumber: $user->profile->mobile_number,
            avatarUrl: $user->profile->avatar_url
        );

        return new Target(
            id: (string) $user->id,
            type: ResourceType::USER,
            attributes: $attributes
        );
    }

    public static function extractMetadata(string $sourceSystem): Metadata
    {
        return new Metadata(
            sourceSystem: $sourceSystem,
            sourceService: (string) Config::get('app.name', 'user-service'),
            timestamp: now()->toIso8601String(),
            traceId: (string) Context::get('trace_id', 'unknown-trace-id'),
            ipAddress: Context::get('ip_address'),
            userAgent: Context::get('user_agent'),
            clientType: Context::get('client_type'),
        );
    }
}
