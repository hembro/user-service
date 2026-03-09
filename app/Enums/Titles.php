<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum Titles: string
{
    use HasEnumOptions;

    case MR = 'Mr.';
    case MS = 'Ms.';
    case MRS = 'Mrs.';
    case DR = 'Dr.';
}
