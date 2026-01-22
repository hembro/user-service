<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum Permissions: string
{
    // ==========================
    // PROPOSAL: VIEW SCOPES
    // ==========================
    // Super Admin, Planning Officer
    case PMS_PROPOSAL_VIEW_ALL = 'pms.proposal.view-all';

    // Administrator, Records Officer, Division Chief, Senior Officer
    case PMS_PROPOSAL_VIEW_DIVISION = 'pms.proposal.view-division';

    // Project Officer, Program Manager, Technical Reviewer
    case PMS_PROPOSAL_VIEW_ASSIGNED = 'pms.proposal.view-assigned';

    // Proponent
    case PMS_PROPOSAL_VIEW_OWN = 'pms.proposal.view-own';

    // ==========================
    // PROPOSAL: ACTIONS
    // ==========================
    // Proponent: "Submit... proposal"
    case PMS_PROPOSAL_CREATE = 'pms.proposal.create';

    // Records Officer: "Records the concept proposal"
    case PMS_PROPOSAL_RECORD = 'pms.proposal.record';

    // Division Chief, Senior Officer, Program Manager: "Assigns it accordingly"
    case PMS_PROPOSAL_ASSIGN = 'pms.proposal.assign';

    // Division Chief, Senior Officer, Project Officer, Program Manager, Technical Reviewer: "Reviews the proposal"
    case PMS_PROPOSAL_REVIEW = 'pms.proposal.review';

    // Proponent: "Track... their proposal"
    case PMS_PROPOSAL_TRACK = 'pms.proposal.track';

    // Divisoon Chief and Senior Officer: "Approves the clearance for assignment of the proposal"
    case PMS_PROPOSAL_APPROVE_CLEARANCE = 'pms.proposal.approve-clearance';

    // ==========================
    // USER PROFILE: MANAGEMENT
    // ==========================
    // Super Admin: "Modify Every user"
    case PMS_USER_MANAGE_ALL = 'pms.user.manage-all';

    // Administrator: "Modify user profile Division"
    case PMS_USER_MANAGE_DIVISION = 'pms.user.manage-division';

    // Every User: "Modify Own"
    case PMS_USER_MANAGE_OWN = 'pms.user.manage-own';

    // ==========================
    // ROLES & PERMISSIONS: MANAGEMENT
    // ==========================
    // Super Admin: "Assign or modify roles... Every User"
    case PMS_ROLE_MANAGE_ALL = 'pms.role.manage-all';

    // Administrator: "Assign or modify roles... Division"
    case PMS_ROLE_MANAGE_DIVISION = 'pms.role.manage-division';

    // ==========================
    // REPORTS
    // ==========================
    // Planning Officer: "Produce a report"
    case PMS_REPORT_GENERATE = 'pms.report.generate';

    public static function all(): array
    {
        return array_map(fn (Permissions $permission): string => $permission->value, self::cases());
    }

    public function pms(): array
    {
        return [];
    }
}
