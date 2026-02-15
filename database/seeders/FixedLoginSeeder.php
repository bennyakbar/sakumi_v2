<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixedLoginSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $defaultUnitId = Unit::query()->where('code', 'MI')->value('id')
            ?? Unit::query()->orderBy('id')->value('id');

        $adminTuRole = Role::firstOrCreate(['name' => 'admin_tu']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Admin TU: super-admin style full access, including signing/reprinting receipts.
        $adminTuRole->syncPermissions(Permission::all());

        // Staff: standard operational access (view/input) without destructive controls.
        $staffRole->syncPermissions([
            'dashboard.view',
            'master.students.view',
            'master.students.create',
            'master.students.edit',
            'master.classes.view',
            'master.categories.view',
            'master.fee-types.view',
            'master.fee-matrix.view',
            'transactions.view',
            'transactions.create',
            'receipts.view',
            'reports.daily',
            'reports.monthly',
            'reports.arrears',
        ]);

        $adminTuUser = User::updateOrCreate(
            ['email' => 'admin.tu@sakumi.local'],
            [
                'name' => 'Admin TU',
                'password' => Hash::make('AdminTU#2026'),
                'is_active' => true,
                'unit_id' => $defaultUnitId,
            ]
        );
        $adminTuUser->syncRoles([$adminTuRole->name, 'super_admin']);

        $staffUser = User::updateOrCreate(
            ['email' => 'staff@sakumi.local'],
            [
                'name' => 'Staff',
                'password' => Hash::make('Staff#2026'),
                'is_active' => true,
                'unit_id' => $defaultUnitId,
            ]
        );
        $staffUser->syncRoles([$staffRole->name, 'operator_tu']);

        $bendaharaUser = User::updateOrCreate(
            ['email' => 'bendahara@sakumi.local'],
            [
                'name' => 'Bendahara',
                'password' => Hash::make('Bendahara#2026'),
                'is_active' => true,
                'unit_id' => $defaultUnitId,
            ]
        );
        $bendaharaUser->syncRoles(['bendahara']);

        $kepalaSekolahUser = User::updateOrCreate(
            ['email' => 'kepala.sekolah@sakumi.local'],
            [
                'name' => 'Kepala Sekolah',
                'password' => Hash::make('KepalaSekolah#2026'),
                'is_active' => true,
                'unit_id' => $defaultUnitId,
            ]
        );
        $kepalaSekolahUser->syncRoles(['kepala_sekolah']);
    }
}
