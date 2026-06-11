<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\DoctorAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DoctorAvailabilitiesWebTest extends TestCase
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

    public function test_doctor_availability_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/appointments/doctor-availability');

        $response->assertOk();
        $response->assertSee('Doctor Availability');
    }

    public function test_super_admin_can_create_doctor_availability(): void
    {
        $this->actingAsSuperAdmin();

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');

        $response = $this->post('/appointments/doctor-availability', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '13:00',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/appointments/doctor-availability');
        $this->assertDatabaseHas('doctor_availabilities', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '13:00',
            'is_active' => true,
        ]);
    }

    public function test_doctor_can_only_see_own_availability(): void
    {
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Visible Doctor']);
        $doctor->assignRole('doctor');

        $otherDoctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Hidden Doctor']);
        $otherDoctor->assignRole('doctor');

        DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        DoctorAvailability::create([
            'doctor_id' => $otherDoctor->id,
            'day_of_week' => 1,
            'start_time' => '13:00',
            'end_time' => '16:00',
            'is_active' => true,
        ]);

        $this->actingAs($doctor);

        $response = $this->get('/appointments/doctor-availability');

        $response->assertOk();
        $response->assertSee('Visible Doctor');
        $response->assertDontSee('Hidden Doctor');
    }
}
