<?php

namespace Database\Factories;

use App\Models\Billing;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillingPaymentFactory extends Factory
{
    public function definition(): array
    {
        $department = Department::factory()->create();
        $receiver = User::factory()->create(['department_id' => $department->id]);

        return [
            'billing_id' => Billing::factory(),
            'received_by' => $receiver->id,
            'amount' => fake()->randomFloat(2, 10, 5000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'online', 'insurance']),
            'reference' => fake()->optional()->bothify('REF-#####'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
