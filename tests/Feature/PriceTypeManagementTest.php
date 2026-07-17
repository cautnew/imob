<?php

use App\Models\Company;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingPriceTypeAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view, create, edit and delete a price type', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('price-types.index'))->assertOk();
    $this->actingAs($owner)->get(route('price-types.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('price-types.store'), [
        'name' => 'Aluguel',
        'comparable' => true,
    ]);
    $response->assertRedirect(route('price-types.index'));

    $priceType = PriceType::where('company_id', $owner->company_id)->where('name', 'Aluguel')->first();
    expect($priceType)->not->toBeNull();
    expect($priceType->comparable)->toBeTrue();

    $this->actingAs($owner)->get(route('price-types.edit', $priceType))->assertOk();

    $this->actingAs($owner)->put(route('price-types.update', $priceType), [
        'name' => 'Aluguel mensal',
        'comparable' => false,
    ])->assertRedirect(route('price-types.index'));

    $priceType->refresh();
    expect($priceType->name)->toBe('Aluguel mensal');
    expect($priceType->comparable)->toBeFalse();

    $this->actingAs($owner)->delete(route('price-types.destroy', $priceType))
        ->assertRedirect(route('price-types.index'));
    expect(PriceType::find($priceType->id))->toBeNull();
});

test('a price type cannot be created with a duplicate name in the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    PriceType::factory()->for($owner->company)->create(['name' => 'Venda']);

    $this->actingAs($owner)->post(route('price-types.store'), [
        'name' => 'Venda',
    ])->assertInvalid(['name']);
});

test('two companies can each have a price type with the same name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $priceTypeA = PriceType::factory()->for($companyA)->create(['name' => 'Venda']);
    $priceTypeB = PriceType::factory()->for($companyB)->create(['name' => 'Venda']);

    expect($priceTypeA->id)->not->toBe($priceTypeB->id);
    expect($priceTypeA->name)->toBe($priceTypeB->name);
});

test('a price type linked to a property cannot be deleted', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    $property = Property::factory()->for($owner->company)->create();
    $property->prices()->create(['price_type_id' => $priceType->id, 'amount' => 100, 'frequency' => 'unico']);

    $this->actingAs($owner)->delete(route('price-types.destroy', $priceType))
        ->assertStatus(422);
    expect(PriceType::find($priceType->id))->not->toBeNull();
});

test('a user without permission cannot access any price type management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $priceType = PriceType::factory()->for($company)->create();

    $this->actingAs($member)->get(route('price-types.index'))->assertForbidden();
    $this->actingAs($member)->get(route('price-types.create'))->assertForbidden();
    $this->actingAs($member)->post(route('price-types.store'), ['name' => 'Novo'])->assertForbidden();
    $this->actingAs($member)->get(route('price-types.edit', $priceType))->assertForbidden();
    $this->actingAs($member)->put(route('price-types.update', $priceType), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($member)->delete(route('price-types.destroy', $priceType))->assertForbidden();
});

test('a company administrator cannot edit a price type from another company', function () {
    $admin = actingPriceTypeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherPriceType = PriceType::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('price-types.edit', $otherPriceType))->assertForbidden();
});

test('a company administrator cannot update a price type from another company', function () {
    $admin = actingPriceTypeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherPriceType = PriceType::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->put(route('price-types.update', $otherPriceType), [
        'name' => 'Hacked',
    ])->assertForbidden();
    expect($otherPriceType->fresh()->name)->toBe($otherPriceType->name);
});

test('a company administrator cannot delete a price type from another company', function () {
    $admin = actingPriceTypeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherPriceType = PriceType::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('price-types.destroy', $otherPriceType))->assertForbidden();
    expect($otherPriceType->fresh())->not->toBeNull();
});

test('a company administrator never sees price types from another company in the index', function () {
    $admin = actingPriceTypeAdministrator();
    PriceType::factory()->for($admin->company)->create();

    $otherCompany = Company::factory()->create();
    PriceType::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('price-types.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('price-types/index')
            ->has('priceTypes', 1)
            ->where('priceTypes.0.company_id', $admin->company_id)
        );
});
