<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Users\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

final class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }
    }
}
