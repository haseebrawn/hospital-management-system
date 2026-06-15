<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseARouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->post('/register', [
            'name' => 'Public User',
            'email' => 'public@example.com',
            'department' => 'Reception',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/login')
            ->assertSessionHas('status');
    }

    public function test_receptionist_can_access_patient_and_appointment_pages_only(): void
    {
        $this->actingAs($this->userWithRole('receptionist'));

        $this->get('/patients')->assertOk();
        $this->get('/appointments')->assertOk();
        $this->get('/lab-tests')->assertForbidden();
        $this->get('/billing')->assertForbidden();
        $this->get('/pharmacy/medicines')->assertForbidden();
    }

    public function test_pharmacist_access_is_limited_to_pharmacy_and_reports(): void
    {
        $this->actingAs($this->userWithRole('pharmacist'));

        $this->get('/pharmacy/medicines')->assertOk();
        $this->get('/pharmacy/dispense')->assertOk();
        $this->get('/pharmacy/ledger')->assertOk();
        $this->get('/reports')->assertOk();
        $this->get('/patients')->assertForbidden();
        $this->get('/billing')->assertForbidden();
    }

    public function test_accountant_access_is_limited_to_billing_and_reports(): void
    {
        $this->actingAs($this->userWithRole('accountant'));

        $this->get('/billing')->assertOk();
        $this->get('/reports')->assertOk();
        $this->get('/patients')->assertForbidden();
        $this->get('/pharmacy/medicines')->assertForbidden();
    }

    public function test_department_admin_cannot_access_system_pages(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->get('/admin/users')->assertOk();
        $this->get('/admin/appointments')->assertOk();
        $this->get('/system/backups')->assertForbidden();
        $this->get('/system/logs/activity')->assertForbidden();
    }

    public function test_super_admin_can_access_system_pages(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $this->get('/system/backups')->assertOk();
        $this->get('/system/logs/activity')->assertOk();
    }
}
