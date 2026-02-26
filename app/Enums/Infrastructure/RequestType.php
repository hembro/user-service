<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum RequestType: string
{
    case EMAIL_CHANGE_REQUEST = 'email_change_request';
    case DEVICE_VERIFICATION_REQUEST = 'device_verification_request';
    case PASSWORD_RESET_REQUEST = 'password_reset_request';
    case TWO_FACTOR_ENABLE_REQUEST = '2fa_enable_request';
}
