<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_with_summary_cards(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Total Patients');
        $response->assertSee('Recent Appointments');
    }
}
