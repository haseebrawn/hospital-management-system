<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BedAllocation>
 */
class BedAllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'bed_id' => null,
            'assigned_at' => now(),
            'released_at' => null,
        ];
    }
}

