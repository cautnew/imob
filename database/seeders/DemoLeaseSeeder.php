<?php

namespace Database\Seeders;

use App\Enums\LeaseAdjustmentIndex;
use App\Enums\LeaseEventType;
use App\Enums\LeaseRenewalType;
use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Property;
use Illuminate\Database\Seeder;

/**
 * Creates a lease for every "alugado" property seeded by DemoPropertySeeder,
 * pairing it with one of the demo company's inquilinos and the property's
 * proprietário, and recording the contract's creation event.
 */
class DemoLeaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', DemoCompanySeeder::COMPANY_SLUG)->firstOrFail();

        $lessees = $company->lessees()->orderBy('id')->get();

        $rentedProperties = $company->properties()
            ->where('status', PropertyStatus::Rented)
            ->with(['owners', 'prices.priceType'])
            ->get();

        foreach ($rentedProperties as $index => $property) {
            $lessee = $lessees[$index % $lessees->count()];
            $owner = $property->owners->first();

            $rentPrice = $property->prices->first(fn ($price) => $price->priceType?->name === 'Aluguel');
            $rentAmount = $rentPrice === null ? fake()->randomFloat(2, 1500, 8000) : $rentPrice->amount;

            $lease = $this->createLease($company, $property, $owner->id, $lessee->id, (float) $rentAmount);

            $property->lessees()->syncWithoutDetaching([$lessee->id]);

            $this->recordCreationEvent($lease);
        }
    }

    private function createLease(Company $company, Property $property, int $ownerId, int $lesseeId, float $rentAmount): Lease
    {
        $startDate = now()->subMonths(fake()->numberBetween(4, 20))->startOfMonth();

        return $company->leases()->create([
            'property_id' => $property->id,
            'owner_id' => $ownerId,
            'lessee_id' => $lesseeId,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addMonths(30),
            'rent_amount' => $rentAmount,
            'adjustment_index' => fake()->randomElement(LeaseAdjustmentIndex::cases()),
            'adjustment_interval_months' => 12,
            'renewal_type' => fake()->randomElement(LeaseRenewalType::cases()),
            'status' => LeaseStatus::Active,
        ]);
    }

    private function recordCreationEvent(Lease $lease): void
    {
        $lease->events()->create([
            'type' => LeaseEventType::Created,
            'occurred_on' => $lease->start_date,
            'description' => sprintf(
                'Contrato criado com aluguel de R$ %s, vigência de %s a %s.',
                number_format((float) $lease->rent_amount, 2, ',', '.'),
                $lease->start_date->format('d/m/Y'),
                $lease->end_date->format('d/m/Y'),
            ),
        ]);
    }
}
