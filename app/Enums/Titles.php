<?php

declare(strict_types=1);

namespace App\Enums;

enum Titles: string
{
    case MR = 'Mr.';
    case MS = 'Ms.';
    case MRS = 'Mrs.';
    case DR = 'Dr.';
}
