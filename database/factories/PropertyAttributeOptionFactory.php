<?php

namespace Database\Factories;

use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAttributeOption>
 */
class PropertyAttributeOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_attribute_id' => PropertyAttribute::factory()->select(),
            'value' => ucfirst(fake()->unique()->word()),
            'order' => 0,
        ];
    }
}
