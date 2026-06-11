<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Patient;
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

        $patient = Patient::factory()->create();

        $response = $this->get("/patients/{$patient->id}");

        $response->assertOk();
        $response->assertSee($patient->first_name);
        $response->assertSee($patient->mrn);
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
