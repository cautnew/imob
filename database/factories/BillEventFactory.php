<?php

namespace Database\Factories;

use App\Enums\BillEventType;
use App\Models\Bill;
use App\Models\BillEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillEvent>
 */
class BillEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'type' => BillEventType::Created,
            'occurred_on' => now(),
            'description' => fake()->sentence(),
        ];
    }
}
