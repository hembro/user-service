<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Roles;
use App\Models\User;
use App\Services\Users\SystemRoleResolver;
use jeremyaliparo\Foundation\Enums\System;

final class UserPolicy
{
    public function __construct(
        private readonly SystemRoleResolver $roleResolver
    ) {}

    public function viewAny(User $actor): bool
    {
        $permissions = array_map(
            fn (System $system) => $this->roleResolver->userManagementPermissionFor($system),
            System::cases()
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

        foreach (System::cases() as $system) {

            if (! $actor->belongsToSystem($system)) {
                continue;
            }

            if ($target->belongsToSystem($system)) {

                $permission = match ($type) {
                    'user' => $this->roleResolver->userManagementPermissionFor($system),
                    'role' => $this->roleResolver->roleManagementPermissionFor($system),
                };

                if ($actor->hasPermissionTo($permission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
