<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftsWebTest extends TestCase
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

    public function test_shifts_index_loads(): void
    {
        $this->actingAsHrManager();

        $response = $this->get('/shifts');

        $response->assertOk();
        $response->assertSee('Shifts');
    }

    public function test_shift_can_be_assigned_from_web(): void
    {
        $this->actingAsHrManager();

        $department = Department::factory()->create();
        $staffUser = User::factory()->create(['department_id' => $department->id]);
        $staff = Staff::factory()->create(['user_id' => $staffUser->id, 'department_id' => $department->id]);

        $response = $this->post('/shifts/assign', [
            'staff_id' => $staff->id,
            'shift_name' => 'Morning',
            'shift_start' => '09:00',
            'shift_end' => '17:00',
            'shift_date' => '2026-06-01',
        ]);

        $response->assertRedirect('/shifts');
        $this->assertDatabaseHas('staff_shifts', [
            'staff_id' => $staff->id,
            'shift_name' => 'Morning',
            'shift_date' => '2026-06-01',
        ]);
        $this->assertNotNull(StaffShift::query()->first());
    }
}

