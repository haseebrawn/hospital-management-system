<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsHrManager(): User
    {
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('hr_manager');

        $this->actingAs($user);

        return $user;
    }

    public function test_staff_index_loads(): void
    {
        $this->actingAsHrManager();

        $response = $this->get('/staff');

        $response->assertOk();
        $response->assertSee('Staff');
    }

    public function test_staff_profile_can_be_created_from_web(): void
    {
        $this->actingAsHrManager();

        $department = Department::factory()->create();
        $staffUser = User::factory()->create(['department_id' => $department->id]);

        $response = $this->post('/staff', [
            'user_id' => $staffUser->id,
            'department_id' => $department->id,
            'designation' => 'Nurse',
            'salary' => 1000,
            'joining_date' => '2026-06-01',
            'employment_status' => 'active',
        ]);

        $staff = Staff::query()->first();
        $this->assertNotNull($staff);

        $response->assertRedirect("/staff/{$staff->id}");
        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'user_id' => $staffUser->id,
            'designation' => 'Nurse',
            'employment_status' => 'active',
        ]);
    }
}

