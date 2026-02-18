<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum AuthResultStatus: string
{
    case AUTHENTICATED = 'authenticated';
    case REQUIRES_CHALLENGE = 'requires_challenge';
}
