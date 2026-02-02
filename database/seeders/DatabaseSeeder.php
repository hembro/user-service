<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()
            ->has(UserProfile::factory(), 'profile')
            ->create([
                'status' => UserStatus::ACTIVE,
                'email' => 'test@example.com',
            ]);

        $this->call(
            class: [RoleAndPermissionSeeder::class]
        );
    }
}
