<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\FeatureCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feature_category_id' => FeatureCategory::factory(),
            'name' => ucfirst(fake()->unique()->word().' '.fake()->word()),
            'active' => true,
        ];
    }

    /**
     * Indicate that the feature is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
