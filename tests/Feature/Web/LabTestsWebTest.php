<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LabTestsWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsLabTech(): User
    {
        Role::firstOrCreate(['name' => 'lab_technician', 'guard_name' => 'api']);

        $dept = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $dept->id]);
        $user->assignRole('lab_technician');

        $this->actingAs($user);

        return $user;
    }

    public function test_lab_tests_index_loads(): void
    {
        $this->actingAsLabTech();

        $this->get('/lab-tests')->assertOk()->assertSee('Lab Tests');
    }

    public function test_lab_tests_index_shows_mini_workflow_preview(): void
    {
        $tech = $this->actingAsLabTech();
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');

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
            'diagnosis' => 'Workflow diagnosis',
        ]);

        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'description' => 'Workflow prescription',
        ]);

        $labTest = LabTest::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'lab_technician_id' => $tech->id,
            'status' => 'completed',
            'test_type' => 'CBC',
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

        $response = $this->get('/lab-tests');

        $response->assertOk();
        $response->assertSee('Workflow');
        $response->assertSee('Open appointment');
        $response->assertSee('Check In');
        $response->assertSee('Medical Record');
        $response->assertSee('Prescription');
        $response->assertSee('Lab Test');
        $response->assertSee('Billing');
    }

    public function test_lab_test_can_be_created_from_web(): void
    {
        $tech = $this->actingAsLabTech();

        $patient = Patient::factory()->create();

        $response = $this->post('/lab-tests', [
            'patient_id' => $patient->id,
            'doctor_id' => null,
            'lab_technician_id' => $tech->id,
            'test_type' => 'CBC',
            'results' => '',
            'status' => 'pending',
        ]);

        $response->assertRedirect('/lab-tests');
        $this->assertDatabaseHas('lab_tests', [
            'patient_id' => $patient->id,
            'lab_technician_id' => $tech->id,
            'test_type' => 'CBC',
            'status' => 'pending',
        ]);
    }

    public function test_appointment_workflow_prefills_lab_test_form_context(): void
    {
        $this->actingAsLabTech();
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Workflow Doctor']);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'reason' => 'Chest pain',
            'notes' => 'Needs ECG',
            'status' => 'completed',
        ]);

        $response = $this->get("/lab-tests/create?appointment_id={$appointment->id}");

        $response->assertOk();
        $response->assertSee('Linked Appointment Context');
        $response->assertSee((string) $appointment->id);
        $response->assertSee('Chest pain');
        $response->assertSee('Needs ECG');
        $response->assertSee('Workflow Doctor');
        $response->assertSee((string) $patient->id);
    }

    public function test_lab_test_show_loads(): void
    {
        $tech = $this->actingAsLabTech();

        $labTest = LabTest::factory()->create([
            'patient_id' => Patient::factory()->create()->id,
            'lab_technician_id' => $tech->id,
            'status' => 'pending',
        ]);

        $this->get("/lab-tests/{$labTest->id}")
            ->assertOk()
            ->assertSee($labTest->test_type);
    }
}
