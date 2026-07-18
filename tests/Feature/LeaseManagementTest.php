<?php

use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingLeaseAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

/**
 * @return array{property: Property, owner: Owner, lessee: Lessee}
 */
function leaseParties(Company $company): array
{
    return [
        'property' => Property::factory()->for($company)->create(),
        'owner' => Owner::factory()->for($company)->create(),
        'lessee' => Lessee::factory()->for($company)->create(),
    ];
}

/**
 * @return array{property_id: int, owner_id: int, lessee_id: int}
 */
function leasePartyIds(Company $company): array
{
    $parties = leaseParties($company);

    return [
        'property_id' => $parties['property']->id,
        'owner_id' => $parties['owner']->id,
        'lessee_id' => $parties['lessee']->id,
    ];
}

/**
 * @return array<string, mixed>
 */
function validLeasePayload(array $overrides = []): array
{
    return array_merge([
        'start_date' => '2026-01-01',
        'end_date' => '2027-01-01',
        'rent_amount' => '2500.00',
        'adjustment_index' => 'igpm',
        'adjustment_interval_months' => 12,
        'renewal_type' => 'automatica',
        'notes' => 'Contrato padrão',
    ], $overrides);
}

test('an owner can view, create, edit and delete a locação', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'owner' => $ownerParty, 'lessee' => $lessee] = leaseParties($owner->company);

    $this->actingAs($owner)->get(route('leases.index'))->assertOk();
    $this->actingAs($owner)->get(route('leases.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
    ]));

    $lease = Lease::where('company_id', $owner->company_id)->first();
    expect($lease)->not->toBeNull();
    $response->assertRedirect(route('leases.show', $lease));
    expect($lease->status->value)->toBe('ativo');

    $this->actingAs($owner)->get(route('leases.show', $lease))->assertOk();
    $this->actingAs($owner)->get(route('leases.edit', $lease))->assertOk();

    $this->actingAs($owner)->put(route('leases.update', $lease), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
        'rent_amount' => '2800.00',
    ]))->assertRedirect(route('leases.show', $lease));

    $lease->refresh();
    expect((float) $lease->rent_amount)->toBe(2800.0);

    $this->actingAs($owner)->delete(route('leases.destroy', $lease))
        ->assertRedirect(route('leases.index'));
    expect(Lease::find($lease->id))->toBeNull();
});

test('creating a locação logs a creation event in the timeline', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'owner' => $ownerParty, 'lessee' => $lessee] = leaseParties($owner->company);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
    ]));

    $lease = Lease::first();

    $this->actingAs($owner)->get(route('leases.show', $lease))
        ->assertInertia(fn (Assert $page) => $page
            ->component('leases/show')
            ->has('events', 1)
            ->where('events.0.type', 'criado')
        );
});

test('creating a locação links the owner and lessee to the property', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'owner' => $ownerParty, 'lessee' => $lessee] = leaseParties($owner->company);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
    ]));

    expect(DB::table('owner_property')->where('owner_id', $ownerParty->id)->where('property_id', $property->id)->exists())->toBeTrue();
    expect(DB::table('lessee_property')->where('lessee_id', $lessee->id)->where('property_id', $property->id)->exists())->toBeTrue();
});

test('required basic fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => '',
        'owner_id' => '',
        'lessee_id' => '',
        'start_date' => '',
        'end_date' => '',
        'rent_amount' => '',
        'adjustment_index' => '',
        'renewal_type' => '',
    ]))->assertInvalid([
        'property_id', 'owner_id', 'lessee_id', 'start_date', 'end_date', 'rent_amount', 'adjustment_index', 'renewal_type',
    ]);
});

test('end_date must be after start_date', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'owner' => $ownerParty, 'lessee' => $lessee] = leaseParties($owner->company);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-01-01',
    ]))->assertInvalid(['end_date']);
});

test('property_id, owner_id and lessee_id must belong to the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCompany = Company::factory()->create();
    ['property' => $foreignProperty, 'owner' => $foreignOwner, 'lessee' => $foreignLessee] = leaseParties($otherCompany);
    ['property' => $property, 'owner' => $ownerParty, 'lessee' => $lessee] = leaseParties($owner->company);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $foreignProperty->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $lessee->id,
    ]))->assertInvalid(['property_id']);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $foreignOwner->id,
        'lessee_id' => $lessee->id,
    ]))->assertInvalid(['owner_id']);

    $this->actingAs($owner)->post(route('leases.store'), validLeasePayload([
        'property_id' => $property->id,
        'owner_id' => $ownerParty->id,
        'lessee_id' => $foreignLessee->id,
    ]))->assertInvalid(['lessee_id']);
});

test('a user without permission cannot access any locação management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $lease = Lease::factory()->for($company)->create(leasePartyIds($company));

    $this->actingAs($member)->get(route('leases.index'))->assertForbidden();
    $this->actingAs($member)->get(route('leases.create'))->assertForbidden();
    $this->actingAs($member)->post(route('leases.store'), validLeasePayload())->assertForbidden();
    $this->actingAs($member)->get(route('leases.show', $lease))->assertForbidden();
    $this->actingAs($member)->get(route('leases.edit', $lease))->assertForbidden();
    $this->actingAs($member)->put(route('leases.update', $lease), validLeasePayload())->assertForbidden();
    $this->actingAs($member)->delete(route('leases.destroy', $lease))->assertForbidden();
});

test('a company administrator cannot view a locação from another company', function () {
    $admin = actingLeaseAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = Lease::factory()->for($otherCompany)->create(leasePartyIds($otherCompany));

    $this->actingAs($admin)->get(route('leases.show', $otherLease))->assertForbidden();
});

test('a company administrator cannot edit a locação from another company', function () {
    $admin = actingLeaseAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = Lease::factory()->for($otherCompany)->create(leasePartyIds($otherCompany));

    $this->actingAs($admin)->get(route('leases.edit', $otherLease))->assertForbidden();
});

test('a company administrator cannot update a locação from another company', function () {
    $admin = actingLeaseAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = Lease::factory()->for($otherCompany)->create(leasePartyIds($otherCompany));

    $this->actingAs($admin)->put(route('leases.update', $otherLease), validLeasePayload())->assertForbidden();
});

test('a company administrator cannot delete a locação from another company', function () {
    $admin = actingLeaseAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = Lease::factory()->for($otherCompany)->create(leasePartyIds($otherCompany));

    $this->actingAs($admin)->delete(route('leases.destroy', $otherLease))->assertForbidden();
    expect($otherLease->fresh())->not->toBeNull();
});

test('a company administrator never sees locações from another company in the index', function () {
    $admin = actingLeaseAdministrator();
    Lease::factory()->for($admin->company)->create(leasePartyIds($admin->company));

    $otherCompany = Company::factory()->create();
    Lease::factory()->for($otherCompany)->create(leasePartyIds($otherCompany));

    $this->actingAs($admin)->get(route('leases.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('leases/index')
            ->has('leases.data', 1)
        );
});

test('the index can be filtered by search and status', function () {
    $admin = actingLeaseAdministrator();
    $activeParties = leaseParties($admin->company);
    Lease::factory()->for($admin->company)->create([
        'property_id' => $activeParties['property']->id,
        'owner_id' => $activeParties['owner']->id,
        'lessee_id' => $activeParties['lessee']->id,
        'status' => 'ativo',
    ]);

    Lease::factory()->for($admin->company)->create([
        ...leasePartyIds($admin->company),
        'status' => 'encerrado',
    ]);

    $this->actingAs($admin)->get(route('leases.index', ['status' => 'encerrado']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('leases.data', 1)
            ->where('leases.data.0.status', 'encerrado')
        );

    $this->actingAs($admin)->get(route('leases.index', ['search' => $activeParties['property']->title]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('leases.data', 1)
        );
});

test('applying a rent adjustment updates the lease and logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        ...leasePartyIds($owner->company),
        'rent_amount' => 2000,
    ]);

    $response = $this->actingAs($owner)->post(route('leases.adjustments.store', $lease), [
        'rent_amount' => 2200,
        'effective_date' => '2026-06-01',
        'notes' => 'Reajuste anual',
    ]);
    $response->assertRedirect(route('leases.show', $lease));

    $lease->refresh();
    expect((float) $lease->rent_amount)->toBe(2200.0);
    expect($lease->last_adjustment_date->toDateString())->toBe('2026-06-01');
    expect($lease->events()->where('type', 'reajustado')->exists())->toBeTrue();
});

test('renewing a lease extends the end date, reactivates it and logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        ...leasePartyIds($owner->company),
        'end_date' => '2026-01-01',
        'status' => 'vencido',
    ]);

    $response = $this->actingAs($owner)->post(route('leases.renewals.store', $lease), [
        'end_date' => '2027-01-01',
        'notes' => 'Renovado por mais 12 meses',
    ]);
    $response->assertRedirect(route('leases.show', $lease));

    $lease->refresh();
    expect($lease->end_date->toDateString())->toBe('2027-01-01');
    expect($lease->status->value)->toBe('ativo');
    expect($lease->events()->where('type', 'renovado')->exists())->toBeTrue();
});

test('a renewal end date must be after the current end date', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        ...leasePartyIds($owner->company),
        'end_date' => '2026-06-01',
    ]);

    $this->actingAs($owner)->post(route('leases.renewals.store', $lease), [
        'end_date' => '2026-01-01',
    ])->assertInvalid(['end_date']);
});

test('changing the situação updates the lease and logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        ...leasePartyIds($owner->company),
        'status' => 'ativo',
    ]);

    $response = $this->actingAs($owner)->patch(route('leases.status.update', $lease), [
        'status' => 'inadimplente',
        'notes' => 'Atraso no pagamento',
    ]);
    $response->assertRedirect(route('leases.show', $lease));

    $lease->refresh();
    expect($lease->status->value)->toBe('inadimplente');
    expect($lease->events()->where('type', 'situacao_alterada')->exists())->toBeTrue();
});
