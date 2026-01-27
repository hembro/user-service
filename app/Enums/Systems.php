<?php

declare(strict_types=1);

namespace App\Enums;

enum Systems: string
{
    case PMS = 'pms';
    case HERDIN = 'herdin';
    case PHRR = 'phrr';
}
