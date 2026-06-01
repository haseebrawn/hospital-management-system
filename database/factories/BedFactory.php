<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bed>
 */
class BedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ward_id' => null,
            'bed_number' => 'B-' . fake()->unique()->numberBetween(1, 999),
            'status' => fake()->randomElement(['available', 'occupied', 'maintenance']),
        ];
    }
}

