<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAttributeValue>
 */
class PropertyAttributeValueFactory extends Factory
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
            'property_attribute_id' => PropertyAttribute::factory(),
            'value' => fake()->word(),
        ];
    }
}
