<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicinesWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsPharmacist(): User
    {
        Role::firstOrCreate(['name' => 'pharmacist', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('pharmacist');

        $this->actingAs($user);

        return $user;
    }

    public function test_medicines_index_loads(): void
    {
        $this->actingAsPharmacist();

        $response = $this->get('/pharmacy/medicines');

        $response->assertOk();
        $response->assertSee('Medicines');
    }

    public function test_medicine_can_be_created_from_web(): void
    {
        $this->actingAsPharmacist();

        $response = $this->post('/pharmacy/medicines', [
            'name' => 'Paracetamol',
            'description' => '500mg tablet',
            'stock' => 10,
            'reorder_level' => 8,
            'price' => 25.50,
            'expiry_date' => '2026-12-31',
            'status' => 'available',
        ]);

        $response->assertRedirect('/pharmacy/medicines');
        $this->assertDatabaseHas('medicines', [
            'name' => 'Paracetamol',
            'stock' => 10,
            'reorder_level' => 8,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('medicine_stock_movements', [
            'movement_type' => 'opening',
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
        ]);
    }

    public function test_medicine_show_loads(): void
    {
        $this->actingAsPharmacist();

        $medicine = Medicine::factory()->create();

        $response = $this->get("/pharmacy/medicines/{$medicine->id}");

        $response->assertOk();
        $response->assertSee($medicine->name);
    }

    public function test_medicines_index_shows_expiry_and_reorder_alerts(): void
    {
        $this->actingAsPharmacist();

        Medicine::factory()->create([
            'name' => 'Expiring Medicine',
            'stock' => 2,
            'reorder_level' => 5,
            'expiry_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->get('/pharmacy/medicines');

        $response->assertOk();
        $response->assertSee('Expiry and Reorder Alerts');
        $response->assertSee('Expiring Medicine');
        $response->assertSee('Expiring Soon');
        $response->assertSee('Low Stock');
    }
}
