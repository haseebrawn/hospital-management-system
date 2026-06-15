<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    public function definition(): array
    {
        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'status' => 'completed',
        ]);

        return [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'description' => fake()->paragraph(),
            'medicines' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
