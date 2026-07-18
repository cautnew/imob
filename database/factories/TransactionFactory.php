<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'lease_id' => null,
            'transaction_category_id' => TransactionCategory::factory(),
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'due_date' => now(),
            'paid_date' => null,
            'status' => TransactionStatus::Pending,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the transaction has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Paid,
            'paid_date' => now(),
        ]);
    }

    /**
     * Indicate that the transaction is pending but past its due date.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
            'due_date' => now()->subDays(10),
        ]);
    }
}
