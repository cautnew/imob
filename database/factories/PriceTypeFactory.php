<?php

namespace Database\Factories;

use App\Models\PriceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceType>
 */
class PriceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word().' '.fake()->word()),
            'comparable' => false,
        ];
    }

    /**
     * Indicate that the price type should be included in property comparisons.
     */
    public function comparable(): static
    {
        return $this->state(fn (array $attributes) => [
            'comparable' => true,
        ]);
    }
}
