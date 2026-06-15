<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        return [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
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
