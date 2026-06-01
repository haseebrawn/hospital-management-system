<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'department_id' => null,
            'designation' => fake()->jobTitle(),
            'salary' => fake()->randomFloat(2, 0, 500000),
            'joining_date' => fake()->date(),
            'employment_status' => fake()->randomElement(['active', 'terminated', 'resigned']),
        ];
    }
}

