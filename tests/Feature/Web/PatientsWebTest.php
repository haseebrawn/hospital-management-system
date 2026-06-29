<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\MedicalRecord;
use App\Models\LabTest;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientsWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_patients_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/patients');

        $response->assertOk();
        $response->assertSee('Patients');
    }

    public function test_patients_index_shows_latest_workflow_preview(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
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
            'status' => 'completed',
            'checked_in_at' => now(),
        ]);

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'chief_complaint' => 'Headache',
            'diagnosis' => 'Migraine',
        ]);

        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'status' => 'pending',
            'description' => 'Rest and fluids',
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
            'total_amount' => 100,
            'paid_amount' => 100,
            'balance_due' => 0,
            'status' => 'paid',
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Consultation',
            'quantity' => 1,
            'price' => 100,
            'type' => 'appointment',
            'source_type' => 'appointment',
            'source_id' => $appointment->id,
            'source_name' => 'Consultation visit',
        ]);

        $response = $this->get('/patients');

        $response->assertOk();
        $response->assertSee('Workflow');
        $response->assertSee('Open history');
        $response->assertSee('Check In');
        $response->assertSee('Medical Record');
        $response->assertSee('Prescription');
        $response->assertSee('Lab Test');
        $response->assertSee('Billing');
    }

    public function test_patient_can_be_created_from_web(): void
    {
        $this->actingAsSuperAdmin();
        $department = Department::factory()->create();

        $response = $this->post('/patients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'contact_number' => '123456789',
            'gender' => 'male',
            'address' => 'Test Address',
            'department_id' => $department->id,
        ]);

        $response->assertRedirect('/patients');
        $this->assertDatabaseHas('patients', [
            'mrn' => 'HMS-000001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'contact_number' => '123456789',
            'gender' => 'male',
        ]);
    }

    public function test_patient_show_loads(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        $doctor->assignRole('doctor');
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'status' => 'approved',
            'checked_in_at' => now(),
        ]);

        $response = $this->get("/patients/{$patient->id}");

        $response->assertOk();
        $response->assertSee($patient->first_name);
        $response->assertSee($patient->mrn);
        $response->assertSee('Care Workflow Preview');
        $response->assertSee('Open Full History');
        $response->assertSee('Latest Visit');
        $response->assertSee($appointment->date);
    }

    public function test_patient_history_page_loads(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
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
            'status' => 'completed',
        ]);

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'chief_complaint' => 'Fever',
            'diagnosis' => 'Viral fever',
        ]);

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'status' => 'pending',
            'description' => 'Rest and fluids',
        ]);

        $medicine = Medicine::factory()->create(['name' => 'Paracetamol']);
        $prescription->items()->create([
            'medicine_id' => $medicine->id,
            'medicine_name' => $medicine->name,
            'quantity' => 5,
        ]);

        $response = $this->get("/patients/{$patient->id}/history");

        $response->assertOk();
        $response->assertSee('Medical History');
        $response->assertSee('Viral fever');
        $response->assertSee('Rest and fluids');
    }

    public function test_patient_history_shows_appointment_workflow_timeline(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
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
            'status' => 'completed',
            'checked_in_at' => now(),
        ]);

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'chief_complaint' => 'Fever',
            'diagnosis' => 'Viral fever',
        ]);

        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'status' => 'pending',
            'description' => 'Rest and fluids',
        ]);

        LabTest::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'lab_technician_id' => $labTechnician->id,
            'test_type' => 'Blood Test',
            'status' => 'completed',
        ]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $doctor->id,
            'total_amount' => 250,
            'paid_amount' => 250,
            'balance_due' => 0,
            'status' => 'paid',
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Appointment fee',
            'quantity' => 1,
            'price' => 250,
            'type' => 'appointment',
            'source_type' => 'appointment',
            'source_id' => $appointment->id,
            'source_name' => 'General checkup',
        ]);

        $response = $this->get("/patients/{$patient->id}/history");

        $response->assertOk();
        $response->assertSee('Each appointment shows the same care workflow timeline.');
        $response->assertSee('Check In');
        $response->assertSee('Medical Record');
        $response->assertSee('Prescription');
        $response->assertSee('Lab Test');
        $response->assertSee('Billing');
        $response->assertSee('Done');
    }

    public function test_patient_can_be_created_with_custom_unique_mrn(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post('/patients', [
            'mrn' => ' custom-123 ',
            'first_name' => 'Ayesha',
            'last_name' => 'Khan',
            'contact_number' => '03001234567',
            'gender' => 'female',
            'address' => 'Test Address',
            'department_id' => null,
        ]);

        $response->assertRedirect('/patients');
        $this->assertDatabaseHas('patients', [
            'mrn' => 'CUSTOM-123',
            'first_name' => 'Ayesha',
        ]);
    }

    public function test_patient_mrn_must_be_unique(): void
    {
        $this->actingAsSuperAdmin();

        Patient::factory()->create(['mrn' => 'HMS-123456']);

        $response = $this->post('/patients', [
            'mrn' => 'HMS-123456',
            'first_name' => 'Duplicate',
            'last_name' => 'Patient',
            'contact_number' => '03009998877',
            'gender' => 'male',
        ]);

        $response->assertSessionHasErrors('mrn');
    }

    public function test_patients_can_be_searched_by_mrn(): void
    {
        $this->actingAsSuperAdmin();

        Patient::factory()->create([
            'mrn' => 'HMS-777777',
            'first_name' => 'Searchable',
            'last_name' => 'Patient',
        ]);

        $response = $this->get('/patients?q=HMS-777777');

        $response->assertOk();
        $response->assertSee('HMS-777777');
    }
}
