<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Definition of all permissions for the user-service
 */
enum Permissions: string
{
    // ==========================
    // USER PROFILE: MANAGEMENT
    // ==========================

    /**
     * Action: Modify all user profile
     * Roles: Admin
     */
    case PMS_USER_MANAGE_ALL = 'pms.user.manage-all';

    /**
     * Action: Modify user profile within the division
     * Roles: Division Admin
     */
    case PMS_USER_MANAGE_DIVISION = 'pms.user.manage-division';

    /**
     * Action: Modify own user profile
     * Roles: All
     */
    case PMS_USER_MANAGE_OWN = 'pms.user.manage-own';

    // ==========================
    // ROLES & PERMISSIONS: MANAGEMENT
    // ==========================

    /**
     * Action: Assign or modify roles and permissions
     * Roles: Admin
     */
    case PMS_ROLE_MANAGE_ALL = 'pms.role.manage-all';

    /**
     * Action: Assign or modify roles and permissions within Division
     * Roles: Division Admin
     */
    case PMS_ROLE_MANAGE_DIVISION = 'pms.role.manage-division';
}
