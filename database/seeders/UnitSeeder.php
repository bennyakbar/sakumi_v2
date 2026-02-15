<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'MI', 'name' => 'Madrasah Ibtidaiyah'],
            ['code' => 'RA', 'name' => 'Raudhatul Athfal'],
            ['code' => 'DTA', 'name' => 'Diniyah Takmiliyah Awaliyah'],
        ];

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['code' => $unit['code']],
                ['name' => $unit['name'], 'is_active' => true]
            );
        }
    }
}
