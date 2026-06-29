<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Department;
use App\Models\DoctorAvailability;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
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

    private function addAvailability(User $doctor, string $date, string $start = '09:00', string $end = '17:00'): DoctorAvailability
    {
        return DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => Carbon::parse($date)->dayOfWeek,
            'start_time' => $start,
            'end_time' => $end,
            'is_active' => true,
        ]);
    }

    public function test_appointments_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/appointments');

        $response->assertOk();
        $response->assertSee('Appointments');
    }

    public function test_appointments_index_shows_mini_workflow_preview(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'lab_technician', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $labTechnician = User::factory()->create(['department_id' => $department->id]);
        $labTechnician->assignRole('lab_technician');

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'status' => 'approved',
            'checked_in_at' => now(),
        ]);

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Workflow diagnosis',
        ]);

        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'description' => 'Workflow prescription',
        ]);

        LabTest::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'lab_technician_id' => $labTechnician->id,
            'test_type' => 'CBC',
            'status' => 'completed',
        ]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $doctor->id,
            'status' => 'pending',
            'total_amount' => 100,
            'paid_amount' => 0,
            'balance_due' => 100,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Consultation fee',
            'quantity' => 1,
            'price' => 100,
            'type' => 'appointment',
            'source_type' => 'appointment',
            'source_id' => $appointment->id,
            'source_name' => 'Consultation visit',
        ]);

        $response = $this->get('/appointments');

        $response->assertOk();
        $response->assertSee('Workflow');
        $response->assertSee('Check In');
        $response->assertSee('Medical Record');
        $response->assertSee('Prescription');
        $response->assertSee('Lab Test');
        $response->assertSee('Billing');
    }

    public function test_appointment_can_be_created_from_web(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $this->addAvailability($doctor, '2026-05-22');

        $response = $this->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'reason' => 'High fever and headache',
            'notes' => 'Patient requested morning slot.',
            'status' => 'pending',
        ]);

        $response->assertRedirect('/appointments');
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'reason' => 'High fever and headache',
            'notes' => 'Patient requested morning slot.',
            'status' => 'pending',
        ]);
    }

    public function test_appointment_cannot_be_created_outside_doctor_availability(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $this->addAvailability($doctor, '2026-05-22', '09:00', '11:00');

        $response = $this->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '14:30',
            'reason' => 'Outside slot',
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors('doctor_id');
        $this->assertDatabaseMissing('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'Outside slot',
        ]);
    }

    public function test_doctor_cannot_be_double_booked_for_same_date_and_time(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $otherPatient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $this->addAvailability($doctor, '2026-05-22');

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'status' => 'approved',
        ]);

        $response = $this->post('/appointments', [
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'reason' => 'Conflict slot',
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors('doctor_id');
        $this->assertDatabaseMissing('appointments', [
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'Conflict slot',
        ]);
    }

    public function test_cancelled_appointment_does_not_block_same_slot(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $otherPatient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $this->addAvailability($doctor, '2026-05-22');

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'status' => 'cancelled',
        ]);

        $response = $this->post('/appointments', [
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'reason' => 'Replacement slot',
            'status' => 'pending',
        ]);

        $response->assertRedirect('/appointments');
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'Replacement slot',
        ]);
    }

    public function test_appointment_update_ignores_its_own_booking_slot(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $this->addAvailability($doctor, '2026-05-22');

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'status' => 'pending',
        ]);

        $response = $this->put("/appointments/{$appointment->id}", [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'date' => '2026-05-22',
            'time' => '10:30',
            'reason' => 'Updated reason',
            'status' => 'pending',
        ]);

        $response->assertRedirect("/appointments/{$appointment->id}");
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'reason' => 'Updated reason',
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

    public function test_appointment_show_displays_reason_and_notes(): void
    {
        $this->actingAsSuperAdmin();

        $appointment = Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => Patient::factory()->create()->id,
            'reason' => 'Routine follow-up',
            'notes' => 'Bring previous lab report.',
            'status' => 'pending',
        ]);

        $response = $this->get("/appointments/{$appointment->id}");

        $response->assertOk();
        $response->assertSee('Routine follow-up');
        $response->assertSee('Bring previous lab report.');
    }

    public function test_appointment_show_links_to_patient_history_and_care_workflow(): void
    {
        $this->actingAsSuperAdmin();

        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
        ]);

        $response = $this->get("/appointments/{$appointment->id}");

        $response->assertOk();
        $response->assertSee('Patient History');
        $response->assertSee('Open Patient History');
        $response->assertSee('Start Medical Record');
        $response->assertSee('Start Prescription');
    }

    public function test_appointment_show_displays_workflow_timeline(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = Appointment::factory()->create([
            'department_id' => $department->id,
            'patient_id' => $patient->id,
            'status' => 'approved',
        ]);

        $appointment->forceFill(['checked_in_at' => now()])->save();

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => User::factory()->create(['department_id' => $department->id])->id,
            'diagnosis' => 'Timeline diagnosis',
        ]);

        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => User::factory()->create(['department_id' => $department->id])->id,
            'description' => 'Timeline prescription',
        ]);

        LabTest::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => null,
            'lab_technician_id' => User::factory()->create(['department_id' => $department->id])->id,
            'test_type' => 'Timeline CBC',
            'status' => 'pending',
        ]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'pending',
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_due' => 0,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Appointment consultation',
            'quantity' => 1,
            'price' => 0,
            'type' => 'appointment',
            'source_type' => 'appointment',
            'source_id' => $appointment->id,
            'source_name' => 'Timeline visit',
        ]);

        $response = $this->get("/appointments/{$appointment->id}");

        $response->assertOk();
        $response->assertSee('Workflow Timeline');
        $response->assertSee('Check In');
        $response->assertSee('Medical Record');
        $response->assertSee('Prescription');
        $response->assertSee('Lab Test');
        $response->assertSee('Billing');
        $response->assertSee('Done');
    }

    public function test_appointments_can_be_searched_by_reason(): void
    {
        $this->actingAsSuperAdmin();

        Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => Patient::factory()->create()->id,
            'reason' => 'Migraine review',
            'status' => 'pending',
        ]);

        $response = $this->get('/appointments?q=Migraine');

        $response->assertOk();
        $response->assertSee('Migraine review');
    }

    public function test_approved_appointment_can_be_checked_in_and_checked_out(): void
    {
        $this->actingAsSuperAdmin();

        $appointment = Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => Patient::factory()->create()->id,
            'status' => 'approved',
        ]);

        $this->put("/appointments/{$appointment->id}/check-in")
            ->assertRedirect();

        $appointment->refresh();
        $this->assertNotNull($appointment->checked_in_at);
        $this->assertSame('checked_in', $appointment->visit_status);

        $this->put("/appointments/{$appointment->id}/check-out")
            ->assertRedirect();

        $appointment->refresh();
        $this->assertNotNull($appointment->checked_out_at);
        $this->assertSame('completed', $appointment->status);
        $this->assertSame('checked_out', $appointment->visit_status);
    }

    public function test_pending_appointment_cannot_be_checked_in(): void
    {
        $this->actingAsSuperAdmin();

        $appointment = Appointment::factory()->create([
            'department_id' => Department::factory()->create()->id,
            'patient_id' => Patient::factory()->create()->id,
            'status' => 'pending',
        ]);

        $this->put("/appointments/{$appointment->id}/check-in")
            ->assertStatus(422);

        $this->assertNull($appointment->fresh()->checked_in_at);
    }
}
