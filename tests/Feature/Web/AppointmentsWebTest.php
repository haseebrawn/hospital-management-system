<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentsWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_appointments_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/appointments');

        $response->assertOk();
        $response->assertSee('Appointments');
    }

    public function test_appointment_can_be_created_from_web(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');

        $response = $this->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'status' => 'pending',
        ]);

        $response->assertRedirect('/appointments');
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'status' => 'pending',
        ]);
    }

    public function test_appointment_show_loads(): void
    {
        $this->actingAsSuperAdmin();

        $appointment = Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => Patient::factory()->create()->id,
            'status' => 'pending',
        ]);

        $response = $this->get("/appointments/{$appointment->id}");

        $response->assertOk();
        $response->assertSee('Appointment');
    }
}

