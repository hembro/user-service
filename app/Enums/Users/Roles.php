<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum Roles: string
{
    case SUPER_ADMIN = 'super-admin';
    case PMS_ADMIN = 'pms-admin';
    case HERDIN_ADMIN = 'herdin-admin';
    case PHRR_ADMIN = 'phrr-admin';
}
