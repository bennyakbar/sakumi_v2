<?php

namespace Database\Seeders\Testing;

use Database\Seeders\FixedLoginSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UnitSeeder;

class DummyDatabaseSeeder extends TestingSeeder
{
    public function run(): void
    {
        $this->ensureTestingEnvironment();

        $this->call([
            UnitSeeder::class,
            RolePermissionSeeder::class,
            FixedLoginSeeder::class,
            DummyReferenceSeeder::class,
            DummyUsersSeeder::class,
            DummyStudentsSeeder::class,
            DummyObligationsSeeder::class,
            DummyTransactionsSeeder::class,
        ]);
    }
}
