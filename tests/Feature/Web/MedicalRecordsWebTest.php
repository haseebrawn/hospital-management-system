<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicalRecordsWebTest extends TestCase
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

    private function doctorWithAppointment(): array
    {
        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'status' => 'completed',
        ]);

        return [$doctor, $patient, $appointment];
    }

    public function test_medical_records_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/medical-records');

        $response->assertOk();
        $response->assertSee('Medical Records');
    }

    public function test_medical_record_can_be_created_from_web(): void
    {
        $this->actingAsSuperAdmin();
        [$doctor, $patient, $appointment] = $this->doctorWithAppointment();

        $response = $this->post('/medical-records', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'visit_type' => 'consultation',
            'chief_complaint' => 'Fever and headache',
            'diagnosis' => 'Viral fever',
            'vitals' => 'BP 120/80',
            'history' => 'No chronic illness',
            'allergies' => 'None',
            'notes' => 'Rest and fluids',
            'follow_up_date' => now()->addWeek()->toDateString(),
        ]);

        $record = MedicalRecord::first();

        $response->assertRedirect("/medical-records/{$record->id}");
        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Viral fever',
        ]);
    }

    public function test_appointment_workflow_links_prefill_medical_record_form(): void
    {
        $this->actingAsSuperAdmin();
        [$doctor, $patient, $appointment] = $this->doctorWithAppointment();
        $appointment->update([
            'reason' => 'Chest pain',
            'notes' => 'Needs ECG review',
        ]);

        $appointmentResponse = $this->get("/appointments/{$appointment->id}");
        $appointmentResponse->assertOk();
        $appointmentResponse->assertSee('Add Medical Record');
        $appointmentResponse->assertSee('Add Prescription');
        $appointmentResponse->assertSee('Request Lab Test');

        $formResponse = $this->get("/medical-records/create?appointment_id={$appointment->id}");

        $formResponse->assertOk();
        $formResponse->assertSee('Chest pain');
        $formResponse->assertSee('Needs ECG review');
        $formResponse->assertSee((string) $patient->id);
        $formResponse->assertSee((string) $doctor->id);
    }

    public function test_medical_record_show_loads(): void
    {
        $this->actingAsSuperAdmin();
        [$doctor, $patient, $appointment] = $this->doctorWithAppointment();

        $record = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Routine check diagnosis',
        ]);

        $response = $this->get("/medical-records/{$record->id}");

        $response->assertOk();
        $response->assertSee('Routine check diagnosis');
    }

    public function test_doctor_sees_only_own_medical_records(): void
    {
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        [$doctor, $patient, $appointment] = $this->doctorWithAppointment();
        [$otherDoctor, $otherPatient, $otherAppointment] = $this->doctorWithAppointment();

        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Visible diagnosis',
        ]);

        MedicalRecord::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
            'appointment_id' => $otherAppointment->id,
            'diagnosis' => 'Hidden diagnosis',
        ]);

        $this->actingAs($doctor);

        $response = $this->get('/medical-records');

        $response->assertOk();
        $response->assertSee('Visible diagnosis');
        $response->assertDontSee('Hidden diagnosis');
    }
}
