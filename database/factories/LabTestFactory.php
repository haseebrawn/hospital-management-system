<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabTest>
 */
class LabTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'doctor_id' => null,
            'lab_technician_id' => null,
            'test_type' => fake()->words(2, true),
            'results' => null,
            'status' => fake()->randomElement(['pending', 'in_process', 'completed']),
        ];
    }
}

