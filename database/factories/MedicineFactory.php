<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'stock' => fake()->numberBetween(0, 100),
            'price' => fake()->randomFloat(2, 0, 5000),
            'expiry_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['available', 'unavailable']),
        ];
    }
}

