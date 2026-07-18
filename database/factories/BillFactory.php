<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
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
            'status' => BillStatus::Pending,
            'due_date' => now(),
            'paid_date' => null,
            'description' => fake()->sentence(3),
        ];
    }

    /**
     * Indicate that the bill has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BillStatus::Paid,
            'paid_date' => now(),
        ]);
    }

    /**
     * Indicate that the bill is pending but past its due date.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BillStatus::Pending,
            'due_date' => now()->subDays(10),
        ]);
    }
}
