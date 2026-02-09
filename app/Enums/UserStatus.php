<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum UserStatus: string
{
    use EnumOptions;

    case ACTIVE = 'active';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
}
