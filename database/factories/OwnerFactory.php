<?php

namespace Database\Factories;

use App\Enums\BankAccountType;
use App\Models\Owner;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
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
            'document' => self::$documentFaker->unique()->cpf(false),
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
            'bank_name' => fake()->randomElement(['Banco do Brasil', 'Itaú', 'Bradesco', 'Santander', 'Nubank']),
            'bank_agency' => fake()->numerify('####'),
            'bank_account' => fake()->numerify('#######-#'),
            'bank_account_type' => BankAccountType::Checking,
            'pix_key' => fake()->safeEmail(),
        ];
    }
}
