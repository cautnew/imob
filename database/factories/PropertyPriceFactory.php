<?php

namespace Database\Factories;

use App\Enums\PriceFrequency;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyPrice>
 */
class PropertyPriceFactory extends Factory
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
            'price_type_id' => PriceType::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000000),
            'frequency' => fake()->randomElement(PriceFrequency::cases()),
        ];
    }
}
