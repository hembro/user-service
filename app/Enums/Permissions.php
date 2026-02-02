<?php

declare(strict_types=1);

namespace App\Enums;

enum Permissions: string
{
    // ==========================
    // PROPOSAL: VIEW
    // ==========================

    /**
     * Action: View All Proposals
     * Roles: Admin, Records Officer, Planning Officer
     */
    case PMS_PROPOSAL_VIEW_ALL = 'pms.proposal.view-all';

    /**
     * Action: View Division's Proposals
     * Roles: Division Admin, Division Chief, Senior Officer
     */
    case PMS_PROPOSAL_VIEW_DIVISION = 'pms.proposal.view-division';

    /**
     * Action: View Proposals assigned to themselves
     * Roles: Project Officer, Program Manager, Technical Reviewer
     */
    case PMS_PROPOSAL_VIEW_ASSIGNED = 'pms.proposal.view-assigned';

    /**
     * Action: View Own Proposals
     * Roles: Proponent
     */
    case PMS_PROPOSAL_VIEW_OWN = 'pms.proposal.view-own';

    // ==========================
    // PROPOSAL: ACTIONS
    // ==========================

    /**
     * Action: Create Proposal
     * Roles: Proponent
     */
    case PMS_PROPOSAL_CREATE = 'pms.proposal.create';

    /**
     * Action: Record Proposal
     * Roles: Records Officer
     */
    case PMS_PROPOSAL_RECORD = 'pms.proposal.record';

    /**
     * Action: Assign Proposal to Division
     * Roles: Admin, Division Admin, Division Chief
     */
    case PMS_PROPOSAL_ASSIGN_TO_DIVISION = 'pms.proposal.assign-to-division';

    /**
     * Action: Assign Proposal to Division
     * Roles: Admin, Division Admin, Division Chief
     */
    case PMS_PROPOSAL_ASSIGN_TO_SENIOR_OFFICER = 'pms.proposal.assign-to-senior-officer';

    /**
     * Action: Assign Proposal to Division
     * Roles: Admin, Division Admin, Division Chief, Senior Officer
     */
    case PMS_PROPOSAL_ASSIGN_TO_PROJECT_MANAGER = 'pms.proposal.assign-to-project-manager';

    /**
     * Action: Assign Proposal to Division
     * Roles: Admin, Division Admin, Division Chief, Senior Officer, Program Manager
     */
    case PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER = 'pms.proposal.assign-to-project-officer';

    /**
     * Action: Assign Proposal to Project Officer with Clearance of DC or SO
     * Roles: Project Officer
     */
    case PMS_PROPOSAL_ASSIGN_TO_PROJECT_OFFICER_WITH_CLEARANCE = 'pms.proposal.assign-to-project-officer-with-clearance';

    /**
     * Action: Approve the clearance to change assignment of the proposal
     * Roles: Division Chief, Senior Officer
     */
    case PMS_PROPOSAL_APPROVE_CLEARANCE = 'pms.proposal.approve-clearance';

    // ==========================
    // USER PROFILE: MANAGEMENT
    // ==========================

    /**
     * Action: Modify all user profile
     * Roles: Admin
     */
    case PMS_USER_MANAGE_ALL = 'pms.user.manage-all';

    /**
     * Action: Modify user profile within the division
     * Roles: Division Admin
     */
    case PMS_USER_MANAGE_DIVISION = 'pms.user.manage-division';

    /**
     * Action: Modify own user profile
     * Roles: All
     */
    case PMS_USER_MANAGE_OWN = 'pms.user.manage-own';

    // ==========================
    // ROLES & PERMISSIONS: MANAGEMENT
    // ==========================

    /**
     * Action: Assign or modify roles and permissions
     * Roles: Admin
     */
    case PMS_ROLE_MANAGE_ALL = 'pms.role.manage-all';

    /**
     * Action: Assign or modify roles and permissions within Division
     * Roles: Division Admin
     */
    case PMS_ROLE_MANAGE_DIVISION = 'pms.role.manage-division';
}
