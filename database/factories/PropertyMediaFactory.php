<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyMedia>
 */
class PropertyMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->uuid().'.jpg';

        return [
            'property_id' => Property::factory(),
            'disk' => 'public',
            'path' => "properties/media/{$filename}",
            'original_filename' => $filename,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 500_000),
            'caption' => null,
            'is_cover' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that this media entry is the property's cover photo.
     */
    public function cover(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cover' => true,
        ]);
    }
}
