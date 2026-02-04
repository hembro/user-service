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
        if ($actor->id === $target->id) {
            return true;
        }

        return $this->hasAdminAccess($actor, $target);
    }

    public function create(User $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }

        return $this->hasAdminAccess($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        return $this->hasAdminAccess($actor, $target);
    }

    public function updateRole(User $actor, User $target): bool
    {
        return $this->hasAdminAccess($actor, $target);
    }

    public function updateStatus(User $actor, User $target): bool
    {
        return $this->hasAdminAccess($actor, $target);
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->hasAdminAccess($actor, $target);
    }

    private function hasAdminAccess(User $actor, User $target): bool
    {
        foreach (Systems::cases() as $system) {

            if ($actor->belongsToSystem($system) && $target->belongsToSystem($system)) {

                $requiredPermission = match ($system) {
                    Systems::PMS => Permissions::PMS_USER_MANAGE_ALL,
                    Systems::HERDIN => Permissions::HERDIN_USER_MANAGE_ALL,
                    Systems::PHRR => Permissions::PHRR_USER_MANAGE_ALL,
                };

                if ($actor->hasPermissionTo($requiredPermission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
