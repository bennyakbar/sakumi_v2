<?php

namespace Database\Seeders\Testing;

class DummyDatabaseSeeder extends TestingSeeder
{
    public function run(): void
    {
        $this->ensureTestingEnvironment();

        $this->call([
            DummyReferenceSeeder::class,
            DummyUsersSeeder::class,
            DummyStudentsSeeder::class,
            DummyTransactionsSeeder::class,
        ]);
    }
}
