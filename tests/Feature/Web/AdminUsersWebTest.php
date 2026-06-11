<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUsersWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);

        $dept = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $dept->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user);

        return $user;
    }

    private function actingAsDepartmentAdmin(Department $department): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_users_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Users');
    }

    public function test_admin_user_create_page_loads(): void
    {
        $this->actingAsSuperAdmin();
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $response = $this->get('/admin/users/create');

        $response->assertOk();
        $response->assertSee('Create User');
    }

    public function test_super_admin_can_create_doctor_user_with_staff_profile(): void
    {
        $this->actingAsSuperAdmin();
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();

        $response = $this->post('/admin/users', [
            'name' => 'Dr Adeel Khan',
            'email' => 'dr.adeel@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'department_id' => $department->id,
            'role' => 'doctor',
            'create_staff_profile' => 1,
            'designation' => 'Consultant Doctor',
            'salary' => 120000,
            'joining_date' => '2026-06-11',
            'employment_status' => 'active',
        ]);

        $response->assertRedirect('/admin/users');

        $doctor = User::where('email', 'dr.adeel@example.com')->first();
        $this->assertNotNull($doctor);
        $this->assertTrue($doctor->hasRole('doctor'));
        $this->assertDatabaseHas('staff', [
            'user_id' => $doctor->id,
            'department_id' => $department->id,
            'designation' => 'Consultant Doctor',
            'employment_status' => 'active',
        ]);
    }

    public function test_super_admin_can_assign_role_and_update_department(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();
        $target = User::factory()->create(['department_id' => $dept1->id]);

        $this->put("/admin/users/{$target->id}/role", ['role' => 'doctor'])->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole('doctor'));

        $this->put("/admin/users/{$target->id}/department", ['department_id' => $dept2->id])->assertRedirect();
        $this->assertSame($dept2->id, $target->fresh()->department_id);
    }

    public function test_super_admin_can_remove_specific_role(): void
    {
        $this->actingAsSuperAdmin();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        $target = User::factory()->create();
        $target->assignRole('doctor');
        $this->assertTrue($target->hasRole('doctor'));

        $this->delete("/admin/users/{$target->id}/role", ['role' => 'doctor'])->assertRedirect();
        $this->assertFalse($target->fresh()->hasRole('doctor'));
    }

    public function test_department_admin_sees_only_same_department_non_admin_users(): void
    {
        $department = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $this->actingAsDepartmentAdmin($department);

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $sameDepartmentUser = User::factory()->create([
            'name' => 'Same Department Doctor',
            'department_id' => $department->id,
        ]);
        $sameDepartmentUser->assignRole('doctor');

        $otherDepartmentUser = User::factory()->create([
            'name' => 'Other Department Doctor',
            'department_id' => $otherDepartment->id,
        ]);
        $otherDepartmentUser->assignRole('doctor');

        $sameDepartmentAdmin = User::factory()->create([
            'name' => 'Same Department Admin',
            'department_id' => $department->id,
        ]);
        $sameDepartmentAdmin->assignRole('admin');

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Same Department Doctor');
        $response->assertDontSee('Other Department Doctor');
        $response->assertDontSee('Same Department Admin');
    }

    public function test_department_admin_cannot_assign_elevated_or_cross_department_roles(): void
    {
        $department = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $this->actingAsDepartmentAdmin($department);

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $sameDepartmentUser = User::factory()->create(['department_id' => $department->id]);
        $otherDepartmentUser = User::factory()->create(['department_id' => $otherDepartment->id]);

        $this->put("/admin/users/{$sameDepartmentUser->id}/role", ['role' => 'doctor'])->assertRedirect();
        $this->assertTrue($sameDepartmentUser->fresh()->hasRole('doctor'));

        $this->put("/admin/users/{$sameDepartmentUser->id}/role", ['role' => 'admin'])->assertForbidden();
        $this->put("/admin/users/{$otherDepartmentUser->id}/role", ['role' => 'doctor'])->assertForbidden();
    }

    public function test_department_admin_can_create_same_department_doctor_only(): void
    {
        $department = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $this->actingAsDepartmentAdmin($department);

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $this->post('/admin/users', [
            'name' => 'Department Doctor',
            'email' => 'department.doctor@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'department_id' => $department->id,
            'role' => 'doctor',
        ])->assertRedirect('/admin/users');

        $doctor = User::where('email', 'department.doctor@example.com')->first();
        $this->assertNotNull($doctor);
        $this->assertTrue($doctor->hasRole('doctor'));

        $this->post('/admin/users', [
            'name' => 'Cross Department Doctor',
            'email' => 'cross.department@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'department_id' => $otherDepartment->id,
            'role' => 'doctor',
        ])->assertSessionHasErrors('department_id');

        $this->post('/admin/users', [
            'name' => 'Bad Admin',
            'email' => 'bad.admin@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'department_id' => $department->id,
            'role' => 'admin',
        ])->assertSessionHasErrors('role');
    }
}
