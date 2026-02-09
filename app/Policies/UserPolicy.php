<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions;
use App\Enums\Systems;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasAnyPermission([
            Permissions::PMS_USER_MANAGE_ALL,
            Permissions::HERDIN_USER_MANAGE_ALL,
            Permissions::PHRR_USER_MANAGE_ALL,
        ]);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->id === $target->id || $this->canManageUsersInCommonSystem($actor, $target);
    }

    public function create(User $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->id === $target->id || $this->canManageUsersInCommonSystem($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        return $this->canManageUsersInCommonSystem($actor, $target);
    }

    public function restore(User $actor, User $target): bool
    {
        return $this->canManageUsersInCommonSystem($actor, $target);
    }

    public function updateRole(User $actor, User $target): bool
    {
        return $this->canManageRolesInCommonSystem($actor, $target);
    }

    public function updateStatus(User $actor, User $target): bool
    {
        return $this->canManageRolesInCommonSystem($actor, $target);
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->canManageUsersInCommonSystem($actor, $target);
    }

    private function canManageUsersInCommonSystem(User $actor, User $target): bool
    {
        foreach (Systems::cases() as $system) {
            if ($actor->belongsToSystem($system) && $target->belongsToSystem($system)) {
                if ($actor->hasPermissionTo($system->getUserManagementPermission())) {
                    return true;
                }
            }
        }

        return false;
    }

    private function canManageRolesInCommonSystem(User $actor, User $target): bool
    {
        foreach (Systems::cases() as $system) {
            if ($actor->belongsToSystem($system) && $target->belongsToSystem($system)) {
                if ($actor->hasPermissionTo($system->getRoleManagementPermission())) {
                    return true;
                }
            }
        }

        return false;
    }
}
