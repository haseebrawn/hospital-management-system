<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaffShift>
 */
class StaffShiftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => null,
            'shift_name' => fake()->randomElement(['Morning', 'Evening', 'Night']),
            'shift_start' => '09:00:00',
            'shift_end' => '17:00:00',
            'shift_date' => fake()->date(),
        ];
    }
}

