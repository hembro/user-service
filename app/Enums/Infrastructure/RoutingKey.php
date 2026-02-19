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

    /**
     * ----------------------------------------------------------------
     *                              USERS
     * ----------------------------------------------------------------
     */
    case USER_REGISTERED = 'user.registered';
    case USER_AVATAR_UPDATED = 'user.profile.avatar.updated';
    case USER_EMAIL_CHANGED = 'user.email.changed';
    case USER_PROFILE_UPDATED = 'user.profile.updated';
    case USER_PASSWORD_RESET = 'user.password.reset';
    case USER_PASSWORD_UPDATED = 'user.password.updated';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
