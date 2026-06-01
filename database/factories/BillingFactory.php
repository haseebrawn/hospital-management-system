<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'created_by' => null,
            'approved_by' => null,
            'total_amount' => fake()->randomFloat(2, 0, 50000),
            'status' => fake()->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}

