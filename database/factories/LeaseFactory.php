<?php

namespace Database\Factories;

use App\Enums\LeaseAdjustmentIndex;
use App\Enums\LeaseRenewalType;
use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'owner_id' => Owner::factory(),
            'lessee_id' => Lessee::factory(),
            'start_date' => now()->subYear(),
            'end_date' => now()->addYear(),
            'rent_amount' => fake()->randomFloat(2, 800, 15000),
            'adjustment_index' => fake()->randomElement(LeaseAdjustmentIndex::cases()),
            'adjustment_interval_months' => 12,
            'last_adjustment_date' => null,
            'renewal_type' => fake()->randomElement(LeaseRenewalType::cases()),
            'status' => LeaseStatus::Active,
            'notes' => null,
        ];
    }
}
