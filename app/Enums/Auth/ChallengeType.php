<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum ChallengeType: string
{
    case TWO_FACTOR = '2fa_totp';
    case DEVICE_VERIFICATION = 'device_verification';

    public function message(): string
    {
        return match ($this) {
            self::TWO_FACTOR => 'Two-factor authentication required.',
            self::DEVICE_VERIFICATION => 'New device detected. Verification code sent.',
        };
    }
}
