<?php

namespace Database\Factories;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->word().' '.fake()->word().' '.fake()->word()),
            'description' => fake()->paragraph(),
            'purpose' => PropertyPurpose::Sale,
            'type' => PropertyType::Apartment,
            'status' => PropertyStatus::Available,
            'zip_code' => fake()->numerify('#####-###'),
            'street' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'complement' => null,
            'neighborhood' => fake()->word(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS']),
            'latitude' => fake()->latitude(-33, 5),
            'longitude' => fake()->longitude(-73, -34),
            'total_area' => fake()->randomFloat(2, 40, 500),
            'built_area' => fake()->randomFloat(2, 30, 400),
        ];
    }

    /**
     * Indicate that the property is for rent.
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => PropertyPurpose::Rent,
        ]);
    }
}
