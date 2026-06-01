<?php

namespace Tests\Feature\Web;

use App\Models\Bed;
use App\Models\BedAllocation;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WardsBedsWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_wards_index_loads(): void
    {
        $this->actingAsAdmin();

        $response = $this->get('/wards-beds/wards');

        $response->assertOk();
        $response->assertSee('Wards');
    }

    public function test_ward_and_bed_and_allocation_can_be_created_from_web(): void
    {
        $this->actingAsAdmin();

        $department = Department::factory()->create();

        $this->post('/wards-beds/wards', [
            'name' => 'Ward A',
            'department_id' => $department->id,
            'capacity' => 5,
        ])->assertRedirect('/wards-beds/wards');

        $ward = Ward::query()->first();
        $this->assertNotNull($ward);

        $this->post('/wards-beds/beds', [
            'ward_id' => $ward->id,
            'bed_number' => 'A-01',
            'status' => 'available',
        ])->assertRedirect('/wards-beds/beds');

        $bed = Bed::query()->first();
        $this->assertNotNull($bed);

        $patient = Patient::factory()->create();

        $this->post('/wards-beds/allocations/assign', [
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
        ])->assertRedirect('/wards-beds/allocations');

        $allocation = BedAllocation::query()->first();
        $this->assertNotNull($allocation);
        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'occupied',
        ]);
    }

    public function test_allocation_can_be_released_and_transferred_from_web(): void
    {
        $this->actingAsAdmin();

        $dept = Department::factory()->create();
        $ward = Ward::factory()->create(['department_id' => $dept->id, 'capacity' => 10]);

        $bed1 = Bed::factory()->create(['ward_id' => $ward->id, 'bed_number' => 'A-01', 'status' => 'available']);
        $bed2 = Bed::factory()->create(['ward_id' => $ward->id, 'bed_number' => 'A-02', 'status' => 'available']);

        $patient = Patient::factory()->create();

        $this->post('/wards-beds/allocations/assign', [
            'patient_id' => $patient->id,
            'bed_id' => $bed1->id,
        ])->assertRedirect('/wards-beds/allocations');

        $allocation = BedAllocation::query()->firstOrFail();

        // Transfer to bed2
        $this->put("/wards-beds/allocations/{$allocation->id}/transfer", [
            'bed_id' => $bed2->id,
        ])->assertRedirect('/wards-beds/allocations');

        $this->assertDatabaseHas('beds', ['id' => $bed1->id, 'status' => 'available']);
        $this->assertDatabaseHas('beds', ['id' => $bed2->id, 'status' => 'occupied']);
        $this->assertDatabaseCount('bed_allocations', 2);

        $active = BedAllocation::query()->whereNull('released_at')->first();
        $this->assertNotNull($active);
        $this->assertSame($bed2->id, $active->bed_id);

        // Release active allocation
        $this->put("/wards-beds/allocations/{$active->id}/release")->assertRedirect('/wards-beds/allocations');
        $this->assertDatabaseHas('beds', ['id' => $bed2->id, 'status' => 'available']);
    }
}
