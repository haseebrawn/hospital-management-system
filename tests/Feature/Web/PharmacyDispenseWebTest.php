<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyDispenseWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsPharmacist(): User
    {
        Role::firstOrCreate(['name' => 'pharmacist', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('pharmacist');

        $this->actingAs($user);

        return $user;
    }

    public function test_dispense_queue_loads(): void
    {
        $this->actingAsPharmacist();

        $response = $this->get('/pharmacy/dispense');

        $response->assertOk();
        $response->assertSee('Dispense Queue');
    }

    public function test_dispensing_prescription_updates_stock_and_ledger(): void
    {
        $this->actingAsPharmacist();

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = \App\Models\Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'status' => 'completed',
        ]);
        $medicine = Medicine::factory()->create([
            'name' => 'Amoxicillin',
            'stock' => 20,
        ]);

        $prescription = Prescription::factory()->create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'pending',
        ]);

        $prescription->items()->create([
            'medicine_id' => $medicine->id,
            'medicine_name' => $medicine->name,
            'dosage' => '500mg',
            'frequency' => 'Twice daily',
            'duration' => '5 days',
            'quantity' => 3,
            'instructions' => 'After meal',
        ]);

        $response = $this->post("/pharmacy/dispense/{$prescription->id}");

        $response->assertRedirect('/pharmacy/dispense');
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'status' => 'dispensed',
        ]);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock' => 17,
        ]);
        $this->assertDatabaseHas('medicine_stock_movements', [
            'medicine_id' => $medicine->id,
            'prescription_id' => $prescription->id,
            'movement_type' => 'dispense',
            'quantity' => 3,
            'stock_before' => 20,
            'stock_after' => 17,
        ]);
    }
}
