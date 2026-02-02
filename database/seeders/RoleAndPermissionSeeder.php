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

        foreach (Roles::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        foreach (Permissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }
    }
}
