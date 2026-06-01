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
            'status' => fake()->randomElement(['pending', 'approved', 'completed', 'cancelled']),
        ];
    }
}

