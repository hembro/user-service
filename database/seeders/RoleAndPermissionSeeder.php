<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Users\Permissions;
use App\Enums\Users\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
        $superAdmin->givePermissionTo(Permission::all());

        $pmsAdmin = Role::firstOrCreate(['name' => Roles::PMS_ADMIN]);
        $pmsAdmin->givePermissionTo([]);
    }
}
