<?php

namespace Database\Seeders;

use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['user_email' => 'admin@vizagland.com'],
            [
                'user_full_name' => 'Super Admin',
                'user_password' => 'Password@123',
                'user_role' => UserRole::SuperAdmin,
                'user_is_active' => true,
            ],
        );
    }
}
