<?php

namespace Tests\Feature\Web;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillingWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAccountant(): User
    {
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('accountant');

        $this->actingAs($user);

        return $user;
    }

    public function test_billing_index_loads(): void
    {
        $this->actingAsAccountant();

        $response = $this->get('/billing');

        $response->assertOk();
        $response->assertSee('Billing');
    }

    public function test_invoice_can_be_created_from_web(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $response = $this->post('/billing', [
            'patient_id' => $patient->id,
            'items' => [
                [
                    'service_name' => 'Lab CBC',
                    'quantity' => 1,
                    'price' => 100,
                    'type' => 'lab',
                ],
                [
                    'service_name' => 'Medicine XYZ',
                    'quantity' => 2,
                    'price' => 50,
                    'type' => 'medicine',
                ],
            ],
        ]);

        $billing = Billing::query()->first();
        $this->assertNotNull($billing);

        $response->assertRedirect("/billing/{$billing->id}");
        $this->assertDatabaseHas('billings', [
            'id' => $billing->id,
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'status' => 'pending',
            'total_amount' => 200,
        ]);
        $this->assertDatabaseCount('billing_items', 2);
    }

    public function test_invoice_can_be_marked_paid_from_web(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'total_amount' => 10,
            'status' => 'pending',
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Other',
            'quantity' => 1,
            'price' => 10,
            'type' => 'other',
        ]);

        $response = $this->put("/billing/{$billing->id}/pay");

        $response->assertRedirect("/billing/{$billing->id}");
        $this->assertDatabaseHas('billings', [
            'id' => $billing->id,
            'status' => 'paid',
            'approved_by' => $user->id,
        ]);
    }

    public function test_invoice_can_be_cancelled_from_web(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'total_amount' => 10,
            'status' => 'pending',
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Other',
            'quantity' => 1,
            'price' => 10,
            'type' => 'other',
        ]);

        $response = $this->put("/billing/{$billing->id}/cancel");

        $response->assertRedirect("/billing/{$billing->id}");
        $this->assertDatabaseHas('billings', [
            'id' => $billing->id,
            'status' => 'cancelled',
        ]);
    }
}
