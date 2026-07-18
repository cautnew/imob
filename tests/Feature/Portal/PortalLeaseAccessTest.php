<?php

use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;

function portalLeaseForLessee(Company $company, Lessee $lessee): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => $lessee->id,
    ]);
}

test('a lessee can view their own lease', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = portalLeaseForLessee($company, $lessee);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.leases.show', $lease))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/leases/show')
            ->where('lease.id', $lease->id));
});

test('a lessee cannot view another lessee\'s lease in the same company', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $otherLessee = Lessee::factory()->for($company)->create();
    $otherLease = portalLeaseForLessee($company, $otherLessee);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.leases.show', $otherLease))
        ->assertNotFound();
});

test('a lessee cannot view a lease from another company', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();

    $otherCompany = Company::factory()->create();
    $otherLessee = Lessee::factory()->for($otherCompany)->create();
    $otherLease = portalLeaseForLessee($otherCompany, $otherLessee);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.leases.show', $otherLease))
        ->assertNotFound();
});

test('a lessee\'s lease index only lists their own leases', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    portalLeaseForLessee($company, $lessee);

    $otherLessee = Lessee::factory()->for($company)->create();
    portalLeaseForLessee($company, $otherLessee);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.leases.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/leases/index')
            ->has('leases', 1));
});
