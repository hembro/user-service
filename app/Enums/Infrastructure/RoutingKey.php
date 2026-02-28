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
    case SYSTEM_SUSPICIOUS_SESSION = 'system.session.suspicious';

    /**
     * ----------------------------------------------------------------
     *                              ADMIN
     * ----------------------------------------------------------------
     */
    case USER_INVITED = 'user.invited';
    case USER_TRASHED = 'user.trashed';
    case USER_DELETED = 'user.deleted';
    case USER_RESTORED = 'user.restored';
    case USER_STATUS_UPDATED = 'user.status.updated';
    case USER_UPDATED = 'user.updated';
    case USER_IMPERSONATED = 'user.impersonated';
    case USER_ROLE_UPDATED = 'user.role.updated';
    case ADMIN_USER_EMAIL_CHANGED = 'admin.user.email.changed';

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
    case AUTH_USER_LOGGED_IN = 'auth.user.logged_in';
    case AUTH_LOGGED_OUT = 'auth.logged_out';
    case AUTH_PASSWORD_RESET = 'auth.password.reset';
    case AUTH_PASSWORD_RESET_REQUESTED = 'auth.password.reset.requested';
    case AUTH_USER_VERIFIED = 'auth.user.verified';
    case AUTH_VERIFICATION_REQUESTED = 'auth.verification.requested';
    case AUTH_DEVICE_VERIFICATION_REQUESTED = 'auth.device.verification.requested';
    case AUTH_TWO_FACTOR_REQUESTED = 'auth.two_factor.requested';
    case AUTH_TWO_FACTOR_ENABLED = 'auth.two_factor.enabled';
    case AUTH_TWO_FACTOR_DISABLED = 'auth.two_factor.disabled';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
