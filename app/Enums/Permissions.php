<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

/**
 * Definition of all permissions for the user-service
 */
enum Permissions: string
{
    use HasEnumOptions;

    // ==========================
    // USER PROFILE: MANAGEMENT
    // ==========================

    /**
     * Action: Modify all user profile
     * Roles: Admin
     */
    case PMS_USER_MANAGE_ALL = 'pms.user.manage-all';
    case HERDIN_USER_MANAGE_ALL = 'herdin.user.manage-all';
    case PHRR_USER_MANAGE_ALL = 'phrr.user.manage-all';

    // ==========================
    // ROLES & PERMISSIONS: MANAGEMENT
    // ==========================

    /**
     * Action: Assign or modify roles and permissions
     * Roles: Admin
     */
    case PMS_ROLE_MANAGE_ALL = 'pms.role.manage-all';
    case HERDIN_ROLE_MANAGE_ALL = 'herdin.role.manage-all';
    case PHRR_ROLE_MANAGE_ALL = 'phrr.role.manage-all';
}
