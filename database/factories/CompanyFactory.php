<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'document' => fake()->unique()->numerify('##.###.###/####-##'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'onboarded_at' => now(),
        ];
    }

    /**
     * Indicate that the company has not completed onboarding yet.
     */
    public function pendingOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'document' => null,
            'phone' => null,
            'address' => null,
            'onboarded_at' => null,
        ]);
    }
}
