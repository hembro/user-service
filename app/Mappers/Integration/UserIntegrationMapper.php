<?php

declare(strict_types=1);

namespace App\Mappers\Integration;

use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;

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
}
