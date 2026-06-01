<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\LabTest;
use App\Models\Patient;
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

