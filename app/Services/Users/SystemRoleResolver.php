<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Enums\Permissions;
use App\Enums\Roles;
use InvalidArgumentException;
use jeremyaliparo\Foundation\Enums\System;

final class SystemRoleResolver
{
    /**
     * Resolve the default role for a new user onboarding to a specific system.
     */
    public static function defaultRoleFor(System $system): Roles
    {
        return match ($system) {
            System::PMS => Roles::PMS_PROPONENT,
            System::HERDIN => Roles::HERDIN_USER,
            System::PHRR => Roles::PHRR_USER,
            default => throw new InvalidArgumentException("No default role defined for system: {$system->value}"),
        };
    }

    /**
     * Resolve the overarching user management permission for a given system.
     */
    public static function userManagementPermissionFor(System $system): Permissions
    {
        return match ($system) {
            System::PMS => Permissions::PMS_USER_MANAGE_ALL,
            System::HERDIN => Permissions::HERDIN_USER_MANAGE_ALL,
            System::PHRR => Permissions::PHRR_USER_MANAGE_ALL,
            default => throw new InvalidArgumentException("No user management permission defined for system: {$system->value}"),
        };
    }

    public static function roleManagementPermissionFor(System $system): Permissions
    {
        return match ($system) {
            System::PMS => Permissions::PMS_ROLE_MANAGE_ALL,
            System::HERDIN => Permissions::HERDIN_ROLE_MANAGE_ALL,
            System::PHRR => Permissions::PHRR_ROLE_MANAGE_ALL,
            default => throw new InvalidArgumentException("No role management permission defined for system: {$system->value}"),
        };
    }
}
