<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'doctor_id' => null,
            'appointment_id' => null,
            'visit_type' => 'consultation',
            'chief_complaint' => fake()->sentence(),
            'diagnosis' => fake()->sentence(),
            'vitals' => 'BP 120/80, Pulse 78',
            'history' => fake()->paragraph(),
            'allergies' => 'None',
            'notes' => fake()->paragraph(),
            'follow_up_date' => now()->addWeek()->toDateString(),
        ];
    }
}
