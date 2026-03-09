<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum SocialProviders: string
{
    use HasEnumOptions;

    case GOOGLE = 'google';
}
