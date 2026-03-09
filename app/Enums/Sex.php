<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum Sex: string
{
    use HasEnumOptions;

    case MALE = 'male';
    case FEMALE = 'female';
    case UNKNOWN = 'unknown';
}
