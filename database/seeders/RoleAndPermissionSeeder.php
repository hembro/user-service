<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesData = collect(Roles::cases())
            ->map(fn (Roles $role) => ['name' => $role->value, 'guard_name' => 'api'])
            ->all();

        $permissionsData = collect(Permissions::cases())
            ->map(fn (Permissions $permission) => ['name' => $permission->value, 'guard_name' => 'api'])
            ->all();

        Role::upsert($rolesData, ['name', 'guard_name'], ['name']);
        Permission::upsert($permissionsData, ['name', 'guard_name'], ['name']);

        $roleModels = Role::all()->keyBy('name');

        foreach (Roles::cases() as $roleEnum) {
            $requiredPermissions = $roleEnum->permissions();

            if (empty($requiredPermissions)) {
                continue;
            }

            $permissionNames = array_map(
                callback: fn (Permissions $permission) => $permission->value,
                array: $requiredPermissions
            );

            if ($roleModel = $roleModels->get($roleEnum->value)) {
                $roleModel->syncPermissions($permissionNames);
            }
        }
    }
}
