<?php

namespace Tests\Feature\Web;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\BillingPayment;
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
                    'source_type' => 'lab_test',
                    'source_id' => 7,
                    'source_name' => 'CBC Panel',
                ],
                [
                    'service_name' => 'Medicine XYZ',
                    'quantity' => 2,
                    'price' => 50,
                    'type' => 'medicine',
                    'source_type' => 'medicine',
                    'source_id' => 9,
                    'source_name' => 'Amoxicillin',
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
            'paid_amount' => 0,
            'balance_due' => 200,
        ]);
        $this->assertNotNull($billing->invoice_number);
        $this->assertStringStartsWith('INV-', $billing->invoice_number);
        $this->assertDatabaseHas('billing_items', [
            'billing_id' => $billing->id,
            'source_type' => 'lab_test',
            'source_id' => 7,
            'source_name' => 'CBC Panel',
        ]);
    }

    public function test_invoice_can_receive_partial_payment_from_web(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'total_amount' => 100,
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_due' => 100,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Other',
            'quantity' => 1,
            'price' => 100,
            'type' => 'other',
        ]);

        $response = $this->post("/billing/{$billing->id}/payments", [
            'amount' => 40,
            'payment_method' => 'cash',
            'reference' => 'RCPT-1001',
            'notes' => 'First partial payment',
        ]);

        $response->assertRedirect("/billing/{$billing->id}");
        $this->assertDatabaseHas('billings', [
            'id' => $billing->id,
            'status' => 'partial',
            'payment_method' => 'cash',
            'paid_amount' => 40,
            'balance_due' => 60,
        ]);
        $this->assertDatabaseHas('billing_payments', [
            'billing_id' => $billing->id,
            'received_by' => $user->id,
            'amount' => 40,
            'payment_method' => 'cash',
            'reference' => 'RCPT-1001',
        ]);
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
            'paid_amount' => 0,
            'balance_due' => 10,
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
            'paid_amount' => 10,
            'balance_due' => 0,
        ]);
        $this->assertDatabaseHas('billing_payments', [
            'billing_id' => $billing->id,
            'received_by' => $user->id,
            'amount' => 10,
            'payment_method' => 'cash',
        ]);
    }

    public function test_invoice_receipt_page_loads(): void
    {
        $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'total_amount' => 100,
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_due' => 100,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'X-Ray',
            'quantity' => 1,
            'price' => 100,
            'type' => 'lab',
            'source_type' => 'lab_test',
            'source_id' => 55,
            'source_name' => 'Chest X-Ray',
        ]);

        BillingPayment::factory()->create([
            'billing_id' => $billing->id,
            'amount' => 25,
            'payment_method' => 'cash',
        ]);

        $response = $this->get("/billing/{$billing->id}/receipt");

        $response->assertOk();
        $response->assertSee($billing->invoice_number);
        $response->assertSee('Receipt');
    }

    public function test_invoice_can_be_cancelled_from_web(): void
    {
        $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => auth()->id(),
            'total_amount' => 10,
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_due' => 10,
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
