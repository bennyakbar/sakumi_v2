<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        // Ensure demo users always exist and credentials are reset on every seed.
        $admin = User::updateOrCreate(
            ['email' => 'admin@sakumi.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['super_admin']);

        $bendahara = User::updateOrCreate(
            ['email' => 'bendahara@sakumi.com'],
            [
                'name' => 'Bendahara',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $bendahara->syncRoles(['bendahara']);
    }
}
