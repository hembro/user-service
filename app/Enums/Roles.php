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

    public function permissions(): array
    {
        return match ($this) {
            self::PMS_ADMIN => [
                Permissions::PMS_PROPOSAL_VIEW_ALL,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_SENIOR_OFFICER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_MANAGER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER,
                Permissions::PMS_USER_MANAGE_ALL,
                Permissions::PMS_USER_MANAGE_OWN,
                Permissions::PMS_ROLE_MANAGE_ALL,
            ],

            self::PMS_DIVISION_ADMIN => [
                Permissions::PMS_PROPOSAL_VIEW_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_SENIOR_OFFICER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_MANAGER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER,
                Permissions::PMS_USER_MANAGE_DIVISION,
                Permissions::PMS_ROLE_MANAGE_DIVISION,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_DIVISION_CHIEF => [
                Permissions::PMS_PROPOSAL_VIEW_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_SENIOR_OFFICER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_MANAGER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER,
                Permissions::PMS_PROPOSAL_APPROVE_CLEARANCE,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_SENIOR_OFFICER => [
                Permissions::PMS_PROPOSAL_VIEW_DIVISION,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_MANAGER,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER,
                Permissions::PMS_PROPOSAL_APPROVE_CLEARANCE,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_PROJECT_OFFICER => [
                Permissions::PMS_PROPOSAL_VIEW_ASSIGNED,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER_WITH_CLEARANCE,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_PROGRAM_MANAGER => [
                Permissions::PMS_PROPOSAL_VIEW_ASSIGNED,
                Permissions::PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_PLANNING_OFFICER => [
                Permissions::PMS_PROPOSAL_VIEW_ALL,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_RECORDS_OFFICER => [
                Permissions::PMS_PROPOSAL_VIEW_ALL,
                Permissions::PMS_PROPOSAL_RECORD,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_TECHNICAL_REVIEWER => [
                Permissions::PMS_PROPOSAL_VIEW_ASSIGNED,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            self::PMS_PROPONENT => [
                Permissions::PMS_PROPOSAL_VIEW_OWN,
                Permissions::PMS_PROPOSAL_CREATE,
                Permissions::PMS_USER_MANAGE_OWN,
            ],

            default => [],
        };
    }
}
