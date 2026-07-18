<?php

namespace Database\Factories;

use App\Enums\LeaseEventType;
use App\Models\Lease;
use App\Models\LeaseEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaseEvent>
 */
class LeaseEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lease_id' => Lease::factory(),
            'type' => LeaseEventType::Created,
            'occurred_on' => now(),
            'description' => fake()->sentence(),
        ];
    }
}
