<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Patient;
use App\Models\Billing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $dept = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $dept->id]);
        $user->assignRole('admin');

        $this->actingAs($user);
    }

    public function test_reports_index_loads(): void
    {
        $this->actingAsAdmin();

        $this->get('/reports')->assertOk()->assertSee('Reports');
    }

    public function test_patient_report_page_loads(): void
    {
        $this->actingAsAdmin();

        $this->get('/reports/patients')->assertOk()->assertSee('Patient Report');
    }

    public function test_other_report_pages_load(): void
    {
        $this->actingAsAdmin();

        $this->get('/reports/appointments')->assertOk()->assertSee('Appointment Report');
        $this->get('/reports/billing')->assertOk()->assertSee('Billing Report');
        $this->get('/reports/ward-bed')->assertOk()->assertSee('Ward & Bed Report');
        $this->get('/reports/staff')->assertOk()->assertSee('Staff Report');
    }

    public function test_patient_report_can_export_csv(): void
    {
        $this->actingAsAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id, 'gender' => 'male']);

        $response = $this->get('/reports/patients?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename=patients_report.csv');
    }

    public function test_billing_report_can_filter_by_department(): void
    {
        $this->actingAsAdmin();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        Billing::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'total_amount' => 250,
            'paid_amount' => 250,
            'balance_due' => 0,
        ]);

        $response = $this->get('/reports/billing?department_id=' . $department->id);

        $response->assertOk();
        $response->assertSee((string) $department->id);
    }
}
