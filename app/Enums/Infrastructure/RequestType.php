<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum RequestType: string
{
    case EMAIL_CHANGE = 'email_change';
    case DEVICE_VERIFICATION = 'device_verification';
    case PASSWORD_RESET = 'password_reset';
}
