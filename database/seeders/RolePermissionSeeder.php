<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Master Data
            'master.classes.view', 'master.classes.create', 'master.classes.edit', 'master.classes.delete',
            'master.categories.view', 'master.categories.create', 'master.categories.edit', 'master.categories.delete',
            'master.fee-types.view', 'master.fee-types.create', 'master.fee-types.edit', 'master.fee-types.delete',
            'master.fee-matrix.view', 'master.fee-matrix.create', 'master.fee-matrix.edit', 'master.fee-matrix.delete',
            'master.students.view', 'master.students.create', 'master.students.edit', 'master.students.delete',
            'master.students.import', 'master.students.export',
            // Transactions
            'transactions.view', 'transactions.create', 'transactions.cancel',
            // Receipts
            'receipts.view', 'receipts.print', 'receipts.reprint',
            // Reports
            'reports.daily', 'reports.monthly', 'reports.arrears', 'reports.export',
            // Dashboard
            'dashboard.view',
            // Users & Roles
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage-roles',
            // Settings
            'settings.view', 'settings.edit',
            // Backup
            'backup.view', 'backup.create',
            // Audit Log
            'audit.view',
            // Notifications
            'notifications.view', 'notifications.retry',
            // Health
            'health.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Bendahara — transactions, receipts, reports, fee matrix
        $bendahara = Role::firstOrCreate(['name' => 'bendahara']);
        $bendahara->syncPermissions([
            'dashboard.view',
            'master.students.view',
            'master.fee-types.view',
            'master.fee-matrix.view', 'master.fee-matrix.create', 'master.fee-matrix.edit', 'master.fee-matrix.delete',
            'master.classes.view',
            'master.categories.view',
            'transactions.view', 'transactions.create', 'transactions.cancel',
            'receipts.view', 'receipts.print', 'receipts.reprint',
            'reports.daily', 'reports.monthly', 'reports.arrears', 'reports.export',
        ]);

        // Kepala Sekolah — view-only dashboard, students, transactions, reports
        $kepalaSekolah = Role::firstOrCreate(['name' => 'kepala_sekolah']);
        $kepalaSekolah->syncPermissions([
            'dashboard.view',
            'master.students.view',
            'master.classes.view',
            'master.categories.view',
            'master.fee-types.view',
            'master.fee-matrix.view',
            'transactions.view',
            'receipts.view',
            'reports.daily', 'reports.monthly', 'reports.arrears', 'reports.export',
        ]);

        // Operator TU — students, classes, view transactions/reports
        $operatorTu = Role::firstOrCreate(['name' => 'operator_tu']);
        $operatorTu->syncPermissions([
            'dashboard.view',
            'master.students.view', 'master.students.create', 'master.students.edit', 'master.students.delete',
            'master.students.import', 'master.students.export',
            'master.classes.view', 'master.classes.create', 'master.classes.edit', 'master.classes.delete',
            'master.categories.view', 'master.categories.create', 'master.categories.edit', 'master.categories.delete',
            'master.fee-types.view',
            'master.fee-matrix.view',
            'transactions.view',
            'receipts.view',
            'reports.daily', 'reports.monthly', 'reports.arrears',
        ]);

        // Auditor — view-only all data, audit log
        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->syncPermissions([
            'dashboard.view',
            'master.students.view',
            'master.classes.view',
            'master.categories.view',
            'master.fee-types.view',
            'master.fee-matrix.view',
            'transactions.view',
            'receipts.view',
            'reports.daily', 'reports.monthly', 'reports.arrears', 'reports.export',
            'audit.view',
        ]);
    }
}
