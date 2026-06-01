<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillingItem>
 */
class BillingItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'billing_id' => null,
            'service_name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 0, 5000),
            'quantity' => fake()->numberBetween(1, 5),
            'type' => fake()->randomElement(['lab', 'medicine', 'appointment', 'other']),
        ];
    }
}

