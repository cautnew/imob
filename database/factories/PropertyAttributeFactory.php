<?php

namespace Database\Factories;

use App\Enums\PropertyAttributeType;
use App\Models\PropertyAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAttribute>
 */
class PropertyAttributeFactory extends Factory
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
            'type' => PropertyAttributeType::Text,
            'filterable' => false,
            'comparable' => false,
            'required' => false,
        ];
    }

    /**
     * Indicate that the attribute is a select list with options.
     */
    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PropertyAttributeType::Select,
        ]);
    }
}
