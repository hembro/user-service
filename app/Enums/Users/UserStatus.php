<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
}
