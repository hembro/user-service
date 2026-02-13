<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum GrantType: string
{
    case REFRESH_TOKEN = 'refresh_token';
    case SYSTEM_VERIFIED = 'system_verified';
}
