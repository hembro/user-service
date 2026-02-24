<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum Sex: string
{
    use EnumOptions;

    case MALE = 'male';
    case FEMALE = 'female';
    case UNKNOWN = 'unknown';
}
