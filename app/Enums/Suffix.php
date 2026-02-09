<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum Suffix: string
{
    use EnumOptions;

    case JR = 'Jr.';
    case SR = 'Sr.';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';
    case VI = 'VI';
    case VII = 'VII';
    case VIII = 'VIII';
    case IX = 'IX';
    case X = 'X';
}
