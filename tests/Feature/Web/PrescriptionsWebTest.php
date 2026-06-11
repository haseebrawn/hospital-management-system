<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrescriptionsWebTest extends TestCase
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

    private function appointmentForDoctor(User $doctor): Appointment
    {
        $patient = Patient::factory()->create(['department_id' => $doctor->department_id]);

        return Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'status' => 'completed',
        ]);
    }

    public function test_prescriptions_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/prescriptions');

        $response->assertOk();
        $response->assertSee('Prescriptions');
    }

    public function test_prescription_can_be_created_from_web(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $appointment = $this->appointmentForDoctor($doctor);

        $response = $this->post('/prescriptions', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'description' => 'Viral fever with body pain.',
            'medicines' => 'Paracetamol 500mg twice daily for 3 days.',
            'status' => 'pending',
        ]);

        $prescription = Prescription::first();

        $response->assertRedirect("/prescriptions/{$prescription->id}");
        $this->assertDatabaseHas('prescriptions', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $appointment->patient_id,
            'description' => 'Viral fever with body pain.',
            'status' => 'pending',
        ]);
    }

    public function test_prescription_show_loads(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $appointment = $this->appointmentForDoctor($doctor);

        $prescription = Prescription::factory()->create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $appointment->patient_id,
            'description' => 'Routine care instructions.',
            'status' => 'pending',
        ]);

        $response = $this->get("/prescriptions/{$prescription->id}");

        $response->assertOk();
        $response->assertSee('Routine care instructions.');
    }

    public function test_doctor_sees_only_own_prescriptions(): void
    {
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Visible Doctor']);
        $doctor->assignRole('doctor');
        $otherDoctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Hidden Doctor']);
        $otherDoctor->assignRole('doctor');

        $ownAppointment = $this->appointmentForDoctor($doctor);
        $otherAppointment = $this->appointmentForDoctor($otherDoctor);

        Prescription::factory()->create([
            'appointment_id' => $ownAppointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $ownAppointment->patient_id,
            'description' => 'Visible prescription',
        ]);

        Prescription::factory()->create([
            'appointment_id' => $otherAppointment->id,
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $otherAppointment->patient_id,
            'description' => 'Hidden prescription',
        ]);

        $this->actingAs($doctor);

        $response = $this->get('/prescriptions');

        $response->assertOk();
        $response->assertSee('Visible prescription');
        $response->assertDontSee('Hidden prescription');
    }
}
