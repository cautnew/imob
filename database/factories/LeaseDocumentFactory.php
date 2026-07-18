<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\LeaseDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaseDocument>
 */
class LeaseDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->uuid().'.pdf';

        return [
            'lease_id' => Lease::factory(),
            'name' => fake()->words(3, true),
            'disk' => 'public',
            'path' => "leases/documents/{$filename}",
            'original_filename' => $filename,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(10_000, 500_000),
        ];
    }
}
