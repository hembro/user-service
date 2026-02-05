<?php

declare(strict_types=1);

namespace App\Enums;

enum Roles: string
{
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
     * Get the scopes for a given array of roles
     */
    public static function scopes(array $roles): string
    {
        $values = array_map(fn (Roles $role) => $role->value, $roles);

        return 'scopes:' . implode(',', $values);
    }

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

    /**
     * Get the scope for a given role
     */
    public function scope(): string
    {
        return "scope: {$this->value}";
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
            default => Systems::UNKNOWN_SOURCE,
        };
    }
}
