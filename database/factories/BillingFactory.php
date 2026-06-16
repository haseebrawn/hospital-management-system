<?php

namespace Database\Factories;

use App\Models\Billing;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    public function definition(): array
    {
        $department = Department::factory()->create();
        $creator = User::factory()->create(['department_id' => $department->id]);
        $patient = Patient::factory()->create(['department_id' => $department->id]);
        $amount = fake()->randomFloat(2, 0, 50000);
        $status = fake()->randomElement(['pending', 'partial', 'paid', 'cancelled']);
        $paidAmount = 0;
        $paymentMethod = null;

        if ($status === 'paid') {
            $paidAmount = $amount;
            $paymentMethod = fake()->randomElement(['cash', 'card', 'bank_transfer', 'online', 'insurance']);
        } elseif ($status === 'partial' && $amount > 0) {
            $paidAmount = round(fake()->randomFloat(2, 0.01, max(0.01, $amount - 0.01)), 2);
            $paymentMethod = fake()->randomElement(['cash', 'card', 'bank_transfer', 'online', 'insurance']);
        }

        return [
            'invoice_number' => sprintf('INV-%s-%06d', now()->format('Ymd'), fake()->numberBetween(1, 999999)),
            'patient_id' => $patient->id,
            'created_by' => $creator->id,
            'approved_by' => null,
            'total_amount' => $amount,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'paid_amount' => $paidAmount,
            'balance_due' => max(0, $amount - $paidAmount),
        ];
    }
}
