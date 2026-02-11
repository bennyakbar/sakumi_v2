<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        // Create default Super Admin user
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@sakumi.test',
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        // Create demo Bendahara user
        $bendahara = User::factory()->create([
            'name' => 'Bendahara',
            'email' => 'bendahara@sakumi.test',
            'is_active' => true,
        ]);
        $bendahara->assignRole('bendahara');
    }
}
