<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum RoutingKey: string
{
    case AUDIT_LOG_CREATED = 'audit.log.created';
    case USER_REGISTERED = 'user.registered';
    case USER_INVITED = 'user.invited';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
