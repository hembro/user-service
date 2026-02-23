<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        $permissions = array_map(
            fn (Systems $system) => $system->getUserManagementPermission(),
            Systems::cases()
        );

        return $actor->hasAnyPermission($permissions);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->id === $target->id
            || $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    public function create(User $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->id === $target->id
            || $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        return $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    public function restore(User $actor, User $target): bool
    {
        return $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    public function updateRole(User $actor, User $target): bool
    {
        return $this->hasCommonSystemPermission($actor, $target, 'role');
    }

    public function updateStatus(User $actor, User $target): bool
    {
        return $this->hasCommonSystemPermission($actor, $target, 'role');
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    public function impersonate(User $actor, User $target): bool
    {
        // 1. You cannot impersonate yourself
        if ($actor->id === $target->id) {
            return false;
        }

        // 2. You cannot impersonate another admin (Security Best Practice)
        if ($target->hasAnyRole(Roles::adminRoles())) {
            return false;
        }

        return $this->hasCommonSystemPermission($actor, $target, 'user');
    }

    private function hasCommonSystemPermission(User $actor, User $target, string $type): bool
    {
        $actor->loadMissing('roles');
        $target->loadMissing('roles');

        foreach (Systems::cases() as $system) {

            if (! $actor->belongsToSystem($system)) {
                continue;
            }

            if ($target->belongsToSystem($system)) {

                $permission = match ($type) {
                    'user' => $system->getUserManagementPermission(),
                    'role' => $system->getRoleManagementPermission(),
                };

                if ($actor->hasPermissionTo($permission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
