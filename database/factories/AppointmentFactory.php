<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'doctor_id' => null,
            'department_id' => null,
            'date' => fake()->date(),
            'time' => fake()->time('H:i:s'),
            'reason' => fake()->optional()->sentence(6),
            'notes' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['pending', 'approved', 'completed', 'cancelled']),
            'checked_in_at' => null,
            'checked_out_at' => null,
        ];
    }
}
