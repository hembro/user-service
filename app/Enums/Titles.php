<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum Titles: string
{
    use EnumOptions;

    case MR = 'Mr.';
    case MS = 'Ms.';
    case MRS = 'Mrs.';
    case DR = 'Dr.';
}
