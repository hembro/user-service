<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum Systems: string
{
    use EnumOptions;

    case PMS = 'pms';
    case HERDIN = 'herdin';
    case PHRR = 'phrr';

    public function defaultRole(): Roles
    {
        return match ($this) {
            self::PMS => Roles::PMS_PROPONENT,
            self::HERDIN => Roles::HERDIN_USER,
            self::PHRR => Roles::PHRR_USER,
        };
    }

    public function getUserManagementPermission(): Permissions
    {
        return match ($this) {
            self::PMS => Permissions::PMS_USER_MANAGE_ALL,
            self::HERDIN => Permissions::HERDIN_USER_MANAGE_ALL,
            self::PHRR => Permissions::PHRR_USER_MANAGE_ALL,
        };
    }

    // Explicit Mapping: Who can promote/ban users?
    public function getRoleManagementPermission(): Permissions
    {
        return match ($this) {
            self::PMS => Permissions::PMS_ROLE_MANAGE_ALL,
            self::HERDIN => Permissions::HERDIN_ROLE_MANAGE_ALL,
            self::PHRR => Permissions::PHRR_ROLE_MANAGE_ALL,
        };
    }

    public function uppercase(): string
    {
        return mb_strtoupper($this->value);
    }
}
