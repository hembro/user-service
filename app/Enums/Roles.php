<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;
use InvalidArgumentException;

enum Roles: string
{
    use EnumOptions;

    case PMS_ADMIN = 'pms.admin';
    case PMS_DIVISION_ADMIN = 'pms.division-admin';
    case PMS_DIVISION_CHIEF = 'pms.division-chief';
    case PMS_SENIOR_OFFICER = 'pms.senior-officer';
    case PMS_PROJECT_OFFICER = 'pms.project-officer';
    case PMS_PROGRAM_MANAGER = 'pms.program-manager';
    case PMS_PLANNING_OFFICER = 'pms.planning-officer';
    case PMS_RECORDS_OFFICER = 'pms.record-officer';
    case PMS_TECHNICAL_REVIEWER = 'pms.technical-reviewer';
    case PMS_PROPONENT = 'pms.proponent';

    case HERDIN_ADMIN = 'herdin.admin';
    case HERDIN_USER = 'herdin.user';
    case PHRR_ADMIN = 'phrr.admin';
    case PHRR_USER = 'phrr.user';

    /**
     * Get all roles for a given system
     */
    public static function forSystem(Systems $system, bool $returnString = false): array
    {
        $roles = array_filter(
            self::cases(),
            fn (Roles $role) => $role->system() === $system
        );

        return $returnString ? array_map(fn (Roles $role) => $role->value, $roles) : $roles;
    }

    public static function adminRoles(bool $returnString = false): array
    {
        $adminRoles = [
            self::PMS_ADMIN,
            self::HERDIN_ADMIN,
            self::PHRR_ADMIN,
        ];

        if ($returnString) {
            return array_map(fn (Roles $role) => $role->value, $adminRoles);
        }

        return $adminRoles;
    }

    /** @param array<int, Roles> $roles */
    public static function ensureBelongsToSystem(array $roles, Systems $system): void
    {
        foreach ($roles as $role) {
            if (! str_starts_with($role->value, "{$system->value}.")) {
                throw new InvalidArgumentException(
                    "Security Violation: Role '{$role}' does not belong to system '{$system->value}'."
                );
            }
        }
    }

    public static function getPassportScopes(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (Roles $role) => [$role->value => $role->description()])
            ->toArray();
    }

    public function description(): ?string
    {
        return match ($this) {
            self::PMS_ADMIN => 'PMS Administrator',
            self::PMS_DIVISION_ADMIN => 'PMS Division Admin',
            self::PMS_DIVISION_CHIEF => 'PMS Division Chief',
            self::PMS_SENIOR_OFFICER => 'PMS Senior Officer',
            self::PMS_PROJECT_OFFICER => 'PMS Project Officer',
            self::PMS_PROGRAM_MANAGER => 'PMS Program Manager',
            self::PMS_PLANNING_OFFICER => 'PMS Planning Officer',
            self::PMS_RECORDS_OFFICER => 'PMS Records Officer',
            self::PMS_TECHNICAL_REVIEWER => 'PMS Technical Reviewer',
            self::PMS_PROPONENT => 'PMS Proponent',
            self::HERDIN_ADMIN => 'HERDIN Administrator',
            self::HERDIN_USER => 'HERDIN User',
            self::PHRR_ADMIN => 'PHRR Administrator',
            self::PHRR_USER => 'PHRR User',
            default => null
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::PMS_ADMIN => [
                Permissions::PMS_USER_MANAGE_ALL,
                Permissions::PMS_ROLE_MANAGE_ALL,
            ],

            default => [],
        };
    }

    /**
     * Get the system for a given role
     */
    public function system(): Systems
    {
        return match (true) {
            str_starts_with($this->value, 'pms.') => Systems::PMS,
            str_starts_with($this->value, 'herdin.') => Systems::HERDIN,
            str_starts_with($this->value, 'phrr.') => Systems::PHRR,
        };
    }
}
