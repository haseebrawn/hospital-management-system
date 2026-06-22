<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Medicine;
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
        $medicine = Medicine::factory()->create(['name' => 'Paracetamol']);

        $response = $this->post('/prescriptions', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'description' => 'Viral fever with body pain.',
            'medicines' => 'Paracetamol 500mg twice daily for 3 days.',
            'status' => 'pending',
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'dosage' => '500mg',
                    'frequency' => 'Twice daily',
                    'duration' => '3 days',
                    'quantity' => 6,
                    'instructions' => 'After meal',
                ],
            ],
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
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicine->id,
            'medicine_name' => 'Paracetamol',
            'dosage' => '500mg',
            'frequency' => 'Twice daily',
            'duration' => '3 days',
            'quantity' => 6,
            'instructions' => 'After meal',
        ]);
    }

    public function test_appointment_workflow_prefills_prescription_form_context(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Workflow Doctor']);
        $doctor->assignRole('doctor');
        $appointment = $this->appointmentForDoctor($doctor);
        $appointment->load('patient');
        $appointment->update([
            'reason' => 'Severe headache',
            'notes' => 'Needs medicine review',
        ]);

        $response = $this->get("/prescriptions/create?appointment_id={$appointment->id}");

        $response->assertOk();
        $response->assertSee('Linked Appointment Context');
        $response->assertSee((string) $appointment->id);
        $response->assertSee('Severe headache');
        $response->assertSee('Needs medicine review');
        $response->assertSee('Workflow Doctor');
        $response->assertSee((string) $appointment->patient->id);
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

    public function test_prescription_items_can_be_updated_from_web(): void
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
            'status' => 'pending',
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Old medicine',
            'dosage' => '250mg',
        ]);

        $response = $this->put("/prescriptions/{$prescription->id}", [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'description' => 'Updated care plan.',
            'medicines' => null,
            'status' => 'pending',
            'items' => [
                [
                    'medicine_name' => 'Custom syrup',
                    'dosage' => '10ml',
                    'frequency' => 'Night',
                    'duration' => '5 days',
                    'quantity' => 1,
                    'instructions' => 'Shake well',
                ],
            ],
        ]);

        $response->assertRedirect("/prescriptions/{$prescription->id}");
        $this->assertDatabaseMissing('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Old medicine',
        ]);
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Custom syrup',
            'dosage' => '10ml',
            'frequency' => 'Night',
        ]);
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
