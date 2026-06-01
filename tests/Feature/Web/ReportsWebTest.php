<?php

namespace Tests\Feature\Web;

use App\Models\Department;
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
}
