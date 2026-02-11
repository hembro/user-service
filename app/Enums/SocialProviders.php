<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum SocialProviders: string
{
    use EnumOptions;

    case GOOGLE = 'google';
}
