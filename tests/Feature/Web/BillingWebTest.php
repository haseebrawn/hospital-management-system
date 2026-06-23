<?php

namespace Tests\Feature\Web;

use App\Models\Appointment;
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

    public function test_billing_create_prefills_linked_appointment_context(): void
    {
        $this->actingAsAccountant();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id, 'name' => 'Billing Doctor']);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'reason' => 'Follow-up consultation',
            'notes' => 'Charge consultation and review',
            'status' => 'completed',
        ]);

        $response = $this->get("/billing/create?appointment_id={$appointment->id}");

        $response->assertOk();
        $response->assertSee('Linked Appointment');
        $response->assertSee('Follow-up consultation');
        $response->assertSee('Charge consultation and review');
        $response->assertSee((string) $appointment->id);
        $response->assertSee('appointment');
        $response->assertSee('Billing Doctor');
        $response->assertSee((string) $patient->id);
    }

    public function test_invoice_show_displays_source_chain(): void
    {
        $this->actingAsAccountant();

        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);

        $department = Department::factory()->create();
        $doctor = User::factory()->create(['department_id' => $department->id]);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => auth()->id(),
            'total_amount' => 0,
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_due' => 0,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Consultation',
            'quantity' => 1,
            'price' => 0,
            'type' => 'appointment',
            'source_type' => 'appointment',
            'source_id' => 99,
            'source_name' => 'Follow-up consultation',
        ]);

        $response = $this->get("/billing/{$billing->id}");

        $response->assertOk();
        $response->assertSee('Source Chain');
        $response->assertSee('Appointment #99');
        $response->assertSee('Follow-up consultation');
        $response->assertSee('Consultation');
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

    public function test_invoice_show_displays_payment_summary_for_partial_payments(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'total_amount' => 100,
            'status' => 'partial',
            'paid_amount' => 40,
            'balance_due' => 60,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Consultation',
            'quantity' => 1,
            'price' => 100,
            'type' => 'appointment',
        ]);

        BillingPayment::factory()->create([
            'billing_id' => $billing->id,
            'received_by' => $user->id,
            'amount' => 40,
            'payment_method' => 'cash',
            'reference' => 'PART-001',
            'notes' => 'Partial payment',
        ]);

        $response = $this->get("/billing/{$billing->id}");

        $response->assertOk();
        $response->assertSee('Payment Progress');
        $response->assertSee('40.00');
        $response->assertSee('60.00');
        $response->assertSee('Latest payment');
        $response->assertSee('PART-001');
        $response->assertSee('Partial payment');
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

    public function test_invoice_receipt_displays_source_chain(): void
    {
        $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'total_amount' => 0,
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_due' => 0,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'Lab CBC',
            'quantity' => 1,
            'price' => 0,
            'type' => 'lab',
            'source_type' => 'lab_test',
            'source_id' => 55,
            'source_name' => 'CBC Panel',
        ]);

        $response = $this->get("/billing/{$billing->id}/receipt");

        $response->assertOk();
        $response->assertSee('Source Chain');
        $response->assertSee('Lab Test #55');
        $response->assertSee('CBC Panel');
        $response->assertSee('Lab CBC');
    }

    public function test_invoice_receipt_displays_payment_snapshot(): void
    {
        $user = $this->actingAsAccountant();

        $department = Department::factory()->create();
        $patient = Patient::factory()->create(['department_id' => $department->id]);

        $billing = Billing::factory()->create([
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'total_amount' => 100,
            'status' => 'partial',
            'paid_amount' => 25,
            'balance_due' => 75,
        ]);

        BillingItem::factory()->create([
            'billing_id' => $billing->id,
            'service_name' => 'X-Ray',
            'quantity' => 1,
            'price' => 100,
            'type' => 'lab',
            'source_type' => 'lab_test',
            'source_id' => 42,
            'source_name' => 'Chest X-Ray',
        ]);

        BillingPayment::factory()->create([
            'billing_id' => $billing->id,
            'received_by' => $user->id,
            'amount' => 25,
            'payment_method' => 'card',
            'reference' => 'CARD-009',
            'notes' => 'First installment',
        ]);

        $response = $this->get("/billing/{$billing->id}/receipt");

        $response->assertOk();
        $response->assertSee('Payment Snapshot');
        $response->assertSee('25.00');
        $response->assertSee('75.00');
        $response->assertSee('CARD-009');
        $response->assertSee('First installment');
        $response->assertSee('Card');
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
