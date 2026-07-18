<?php

namespace Database\Factories;

use App\Enums\MaritalStatus;
use App\Models\Lessee;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lessee>
 */
class LesseeFactory extends Factory
{
    private static ?Generator $documentFaker = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        self::$documentFaker ??= FakerFactory::create('pt_BR');

        return [
            'name' => fake()->name(),
            'birth_date' => fake()->date('Y-m-d', '-18 years'),
            'marital_status' => fake()->randomElement(MaritalStatus::cases()),
            'occupation' => fake()->jobTitle(),
            'document' => self::$documentFaker->unique()->cpf(false),
            'rg' => fake()->numerify('##.###.###-#'),
            'rg_issuer' => 'SSP',
            'phone' => fake()->numerify('(##) ####-####'),
            'mobile' => fake()->numerify('(##) 9####-####'),
            'email' => fake()->safeEmail(),
            'zip_code' => fake()->numerify('#####-###'),
            'street' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'complement' => null,
            'neighborhood' => fake()->word(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS']),
            'monthly_income' => fake()->randomFloat(2, 1800, 15000),
        ];
    }
}
