<?php

declare(strict_types=1);

namespace App\Enums;

enum Systems: string
{
    case PMS = 'pms';
    case HERDIN = 'herdin';
    case PHRR = 'phrr';
    case UNKNOWN_SOURCE = 'unknown_source';

    public static function find(string $system): self
    {
        return match ($system) {
            self::PMS->value => self::PMS,
            self::HERDIN->value => self::HERDIN,
            self::PHRR->value => self::PHRR,
            default => self::UNKNOWN_SOURCE,
        };
    }

    public function defaultRole(): Roles
    {
        return match ($this) {
            self::PMS => Roles::PMS_PROPONENT,
            self::HERDIN => Roles::HERDIN_USER,
            self::PHRR => Roles::PHRR_USER,
        };
    }
}
