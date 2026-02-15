<?php

namespace Database\Factories;

use App\Models\StudentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentCategory>
 */
class StudentCategoryFactory extends Factory
{
    protected $model = StudentCategory::class;

    public function definition(): array
    {
        return [
            'code' => 'SC-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Reguler', 'Yatim', 'Dhuafa', 'Prestasi']),
            'description' => fake()->sentence(),
            'discount_percentage' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
