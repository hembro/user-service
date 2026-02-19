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
     *                              USERS
     * ----------------------------------------------------------------
     */
    case USER_INVITED = 'user.invited';

    /**
     * ----------------------------------------------------------------
     *                              ADMIN
     * ----------------------------------------------------------------
     */
    case USER_REGISTERED = 'user.registered';
    case USER_AVATAR_UPDATED = 'user.profile.avatar.updated';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
