<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum Roles: string
{
    case PMS_ADMIN = 'pms.admin';
    case PMS_DIVISION_ADMIN = 'pms.division-admin';
    case PMS_DIVISION_CHIEF = 'pms.division-chief';
    case PMS_SENIOR_OFFICER = 'pms.senior-officer';
    case PMS_PROJECT_OFFICER = 'pms.project-officer';
    case PMS_PROGRAM_MANAGER = 'pms.program-manager';
    case PMS_PLANNING_OFFICER = 'pms.planning-officer';
    case PMS_RECORD_OFFICER = 'pms.record-officer';
    case PMS_TECHNICAL_REVIEWER = 'pms.technical-reviewer';
    case PMS_PROPONENT = 'pms.proponent';

    case HERDIN_ADMIN = 'herdin.admin';
    case PHRR_ADMIN = 'phrr.admin';
}
