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

    public function test_admin_users_index_loads(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Users');
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
}
