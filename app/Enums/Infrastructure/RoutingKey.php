<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum RoutingKey: string
{
    /**
     * ----------------------------------------------------------------
     *                              SYSTEM
     * ----------------------------------------------------------------
     */
    case AUDIT_LOG_CREATED = 'audit.log.created';

    /**
     * ----------------------------------------------------------------
     *                              ADMIN
     * ----------------------------------------------------------------
     */
    case USER_INVITED = 'user.invited';
    case USER_DELETED = 'user.deleted';
    case USER_RESTORED = 'user.restored';
    case USER_STATUS_UPDATED = 'user.status.updated';
    case USER_UPDATED = 'user.updated';

    /**
     * ----------------------------------------------------------------
     *                              USERS
     * ----------------------------------------------------------------
     */
    case USER_REGISTERED = 'user.registered';
    case USER_AVATAR_UPDATED = 'user.profile.avatar.updated';
    case USER_PROFILE_UPDATED = 'user.profile.updated';
    case USER_PASSWORD_RESET = 'user.password.reset';
    case USER_PASSWORD_UPDATED = 'user.password.updated';
    case USER_EMAIL_CHANGE_REQUESTED = 'user.email.change.requested';
    case USER_EMAIL_CHANGED = 'user.email.changed';

    /**
     * ----------------------------------------------------------------
     *                              AUTH
     * ----------------------------------------------------------------
     */
    case AUTH_LOGGED_OUT = 'auth.logged_out';
    case AUTH_PASSWORD_RESET = 'auth.password.reset';
    case AUTH_PASSWORD_RESET_REQUESTED = 'auth.password.reset.requested';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
