<?php

namespace Database\Seeders\Testing;

use Illuminate\Database\Seeder;
use RuntimeException;

abstract class TestingSeeder extends Seeder
{
    protected function ensureTestingEnvironment(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException(static::class.' can only run in APP_ENV=testing');
        }
    }
}
