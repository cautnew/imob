<?php

namespace Database\Factories;

use App\Enums\BillReceiptStatus;
use App\Models\Bill;
use App\Models\BillReceipt;
use App\Models\Lessee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillReceipt>
 */
class BillReceiptFactory extends Factory
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
            'lessee_id' => Lessee::factory(),
            'status' => BillReceiptStatus::Pending,
            'disk' => 'public',
            'path' => 'bill-receipts/'.fake()->uuid().'.pdf',
            'original_filename' => 'comprovante.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
        ];
    }

    /**
     * Indicate that the receipt has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BillReceiptStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the receipt has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BillReceiptStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
