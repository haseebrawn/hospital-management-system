<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => null,
            'doctor_id' => null,
            'patient_id' => null,
            'description' => fake()->paragraph(),
            'medicines' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
