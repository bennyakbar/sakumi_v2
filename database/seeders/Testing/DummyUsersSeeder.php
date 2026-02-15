<?php

namespace Database\Seeders\Testing;

use App\Models\User;

class DummyUsersSeeder extends TestingSeeder
{
    public function run(): void
    {
        $this->ensureTestingEnvironment();

        User::factory()->count(10)->create();
    }
}
