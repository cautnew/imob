<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeValue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingPropertyAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

/**
 * @return array<string, mixed>
 */
function validPropertyPayload(int $priceTypeId, array $overrides = []): array
{
    return array_merge([
        'title' => 'Apartamento com vista para o mar',
        'description' => 'Um belo apartamento.',
        'purpose' => 'venda',
        'type' => 'apartamento',
        'status' => 'disponivel',
        'zip_code' => '01310-000',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'complement' => 'Apto 101',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'latitude' => -23.5613,
        'longitude' => -46.6565,
        'total_area' => 120,
        'built_area' => 100,
        'prices' => [
            ['price_type_id' => $priceTypeId, 'amount' => 850000, 'frequency' => 'unico'],
        ],
    ], $overrides);
}

test('an owner can view, create, edit and delete a property', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->get(route('properties.index'))->assertOk();
    $this->actingAs($owner)->get(route('properties.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id));
    $response->assertRedirect(route('properties.index'));

    $property = Property::where('company_id', $owner->company_id)->first();
    expect($property)->not->toBeNull();
    expect($property->title)->toBe('Apartamento com vista para o mar');
    expect($property->purpose->value)->toBe('venda');
    expect((float) $property->total_area)->toBe(120.0);

    $price = $property->prices()->first();
    expect((float) $price->amount)->toBe(850000.0);
    expect($price->frequency->value)->toBe('unico');
    expect($price->price_type_id)->toBe($priceType->id);

    $this->actingAs($owner)->get(route('properties.edit', $property))->assertOk();

    $this->actingAs($owner)->put(route('properties.update', $property), validPropertyPayload($priceType->id, [
        'title' => 'Apartamento reformado',
        'status' => 'reservado',
    ]))->assertRedirect(route('properties.index'));

    $property->refresh();
    expect($property->title)->toBe('Apartamento reformado');
    expect($property->status->value)->toBe('reservado');

    $this->actingAs($owner)->delete(route('properties.destroy', $property))
        ->assertRedirect(route('properties.index'));
    expect(Property::find($property->id))->toBeNull();
});

test('a property requires at least one price', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'prices' => [],
    ]))->assertInvalid(['prices']);
});

test('price entries validate price_type_id, amount and frequency', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    $otherCompany = Company::factory()->create();
    $foreignPriceType = PriceType::factory()->for($otherCompany)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'prices' => [
            ['price_type_id' => $foreignPriceType->id, 'amount' => 100, 'frequency' => 'unico'],
        ],
    ]))->assertInvalid(['prices.0.price_type_id']);

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'prices' => [
            ['price_type_id' => $priceType->id, 'amount' => null, 'frequency' => 'unico'],
        ],
    ]))->assertInvalid(['prices.0.amount']);

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'prices' => [
            ['price_type_id' => $priceType->id, 'amount' => 100, 'frequency' => 'bimestral'],
        ],
    ]))->assertInvalid(['prices.0.frequency']);
});

test('a property can be created with multiple dynamic prices and frequencies', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $sale = PriceType::factory()->for($owner->company)->create(['name' => 'Venda']);
    $condo = PriceType::factory()->for($owner->company)->create(['name' => 'Condomínio']);
    $iptu = PriceType::factory()->for($owner->company)->create(['name' => 'IPTU']);

    $response = $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($sale->id, [
        'prices' => [
            ['price_type_id' => $sale->id, 'amount' => 850000, 'frequency' => 'unico'],
            ['price_type_id' => $condo->id, 'amount' => 650, 'frequency' => 'mensal'],
            ['price_type_id' => $iptu->id, 'amount' => 2400, 'frequency' => 'anual'],
        ],
    ]));
    $response->assertRedirect(route('properties.index'));

    $property = Property::first();
    expect($property->prices()->count())->toBe(3);

    $condoPrice = $property->prices()->where('price_type_id', $condo->id)->first();
    expect((float) $condoPrice->amount)->toBe(650.0);
    expect($condoPrice->frequency->value)->toBe('mensal');

    $iptuPrice = $property->prices()->where('price_type_id', $iptu->id)->first();
    expect($iptuPrice->frequency->value)->toBe('anual');
});

test('required basic fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'title' => '',
        'zip_code' => '',
        'street' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
        'total_area' => '',
    ]))->assertInvalid(['title', 'zip_code', 'street', 'neighborhood', 'city', 'state', 'total_area']);
});

test('geolocation coordinates must be within valid ranges', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'latitude' => 200,
        'longitude' => -300,
    ]))->assertInvalid(['latitude', 'longitude']);
});

test('a property can be created with dynamic features and attributes', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $category = FeatureCategory::factory()->for($owner->company)->create();
    $pool = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id, 'name' => 'Piscina']);
    $gym = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id, 'name' => 'Academia']);

    $bedrooms = PropertyAttribute::factory()->for($owner->company)->create(['name' => 'Quartos', 'type' => 'inteiro', 'required' => true]);
    $finish = PropertyAttribute::factory()->select()->for($owner->company)->create(['name' => 'Padrão de acabamento']);
    $finish->options()->createMany([
        ['value' => 'Simples', 'order' => 0],
        ['value' => 'Alto padrão', 'order' => 1],
    ]);
    $amenities = PropertyAttribute::factory()->for($owner->company)->create(['name' => 'Comodidades extras', 'type' => 'multiselect']);
    $amenities->options()->createMany([
        ['value' => 'Churrasqueira', 'order' => 0],
        ['value' => 'Varanda', 'order' => 1],
    ]);

    $selectedFinish = $finish->options()->where('value', 'Alto padrão')->first();
    $amenityOptions = $amenities->options()->pluck('id')->all();

    $response = $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'features' => [$pool->id, $gym->id],
        'attributes' => [
            $bedrooms->id => 3,
            $finish->id => $selectedFinish->id,
            $amenities->id => $amenityOptions,
        ],
    ]));
    $response->assertRedirect(route('properties.index'));

    $property = Property::first();
    expect($property->features()->pluck('features.id')->sort()->values()->all())
        ->toBe(collect([$pool->id, $gym->id])->sort()->values()->all());

    $bedroomsValue = $property->attributeValues()->where('property_attribute_id', $bedrooms->id)->first();
    expect($bedroomsValue->value)->toBe('3');

    $finishValue = $property->attributeValues()->where('property_attribute_id', $finish->id)->first();
    expect($finishValue->property_attribute_option_id)->toBe($selectedFinish->id);

    $amenityValues = $property->attributeValues()->where('property_attribute_id', $amenities->id)->pluck('property_attribute_option_id')->sort()->values()->all();
    expect($amenityValues)->toBe(collect($amenityOptions)->sort()->values()->all());
});

test('a required dynamic attribute must be provided', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    $bedrooms = PropertyAttribute::factory()->for($owner->company)->create(['name' => 'Quartos', 'type' => 'inteiro', 'required' => true]);

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id))
        ->assertInvalid(["attributes.{$bedrooms->id}"]);
});

test('updating a property replaces its previous features, attribute values and prices', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $category = FeatureCategory::factory()->for($owner->company)->create();
    $pool = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id]);
    $gym = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id]);
    $bedrooms = PropertyAttribute::factory()->for($owner->company)->create(['type' => 'inteiro']);
    $rentPriceType = PriceType::factory()->for($owner->company)->create();
    $salePriceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($rentPriceType->id, [
        'features' => [$pool->id],
        'attributes' => [$bedrooms->id => 2],
        'prices' => [
            ['price_type_id' => $rentPriceType->id, 'amount' => 3000, 'frequency' => 'mensal'],
        ],
    ]));
    $property = Property::first();

    $this->actingAs($owner)->put(route('properties.update', $property), validPropertyPayload($rentPriceType->id, [
        'features' => [$gym->id],
        'attributes' => [$bedrooms->id => 4],
        'prices' => [
            ['price_type_id' => $salePriceType->id, 'amount' => 500000, 'frequency' => 'unico'],
        ],
    ]))->assertRedirect(route('properties.index'));

    $property->refresh();
    expect($property->features()->pluck('features.id')->all())->toBe([$gym->id]);
    expect($property->attributeValues()->count())->toBe(1);
    expect($property->attributeValues()->first()->value)->toBe('4');

    expect($property->prices()->count())->toBe(1);
    $price = $property->prices()->first();
    expect($price->price_type_id)->toBe($salePriceType->id);
    expect((float) $price->amount)->toBe(500000.0);
    expect($price->frequency->value)->toBe('unico');
});

test('a user without permission cannot access any property management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $property = Property::factory()->for($company)->create();
    $priceType = PriceType::factory()->for($company)->create();

    $this->actingAs($member)->get(route('properties.index'))->assertForbidden();
    $this->actingAs($member)->get(route('properties.create'))->assertForbidden();
    $this->actingAs($member)->post(route('properties.store'), validPropertyPayload($priceType->id))->assertForbidden();
    $this->actingAs($member)->get(route('properties.edit', $property))->assertForbidden();
    $this->actingAs($member)->put(route('properties.update', $property), validPropertyPayload($priceType->id))->assertForbidden();
    $this->actingAs($member)->delete(route('properties.destroy', $property))->assertForbidden();
});

test('a company administrator cannot edit a property from another company', function () {
    $admin = actingPropertyAdministrator();
    $otherCompany = Company::factory()->create();
    $otherProperty = Property::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('properties.edit', $otherProperty))->assertForbidden();
});

test('a company administrator cannot update a property from another company', function () {
    $admin = actingPropertyAdministrator();
    $otherCompany = Company::factory()->create();
    $otherProperty = Property::factory()->for($otherCompany)->create();
    $priceType = PriceType::factory()->for($admin->company)->create();

    $this->actingAs($admin)->put(route('properties.update', $otherProperty), validPropertyPayload($priceType->id, [
        'title' => 'Hacked',
    ]))->assertForbidden();
    expect($otherProperty->fresh()->title)->toBe($otherProperty->title);
});

test('a company administrator cannot delete a property from another company', function () {
    $admin = actingPropertyAdministrator();
    $otherCompany = Company::factory()->create();
    $otherProperty = Property::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('properties.destroy', $otherProperty))->assertForbidden();
    expect($otherProperty->fresh())->not->toBeNull();
});

test('a company administrator never sees properties from another company in the index', function () {
    $admin = actingPropertyAdministrator();
    Property::factory()->for($admin->company)->create();

    $otherCompany = Company::factory()->create();
    Property::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('properties.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('properties/index')
            ->has('properties.data', 1)
            ->where('properties.data.0.id', $admin->company->properties()->first()->id)
        );
});

test('deleting a property removes its feature links, attribute values and prices', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $category = FeatureCategory::factory()->for($owner->company)->create();
    $feature = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id]);
    $bedrooms = PropertyAttribute::factory()->for($owner->company)->create(['type' => 'inteiro']);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'features' => [$feature->id],
        'attributes' => [$bedrooms->id => 2],
    ]));
    $property = Property::first();
    $attributeValueId = $property->attributeValues()->first()->id;
    $priceId = $property->prices()->first()->id;

    $this->actingAs($owner)->delete(route('properties.destroy', $property));

    expect(DB::table('feature_property')->where('property_id', $property->id)->exists())->toBeFalse();
    expect(PropertyAttributeValue::find($attributeValueId))->toBeNull();
    expect(DB::table('property_prices')->where('id', $priceId)->exists())->toBeFalse();
});

test('comparable price types can be filtered for property comparison', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $comparableType = PriceType::factory()->comparable()->for($owner->company)->create();
    $nonComparableType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($comparableType->id, [
        'prices' => [
            ['price_type_id' => $comparableType->id, 'amount' => 850000, 'frequency' => 'unico'],
            ['price_type_id' => $nonComparableType->id, 'amount' => 100, 'frequency' => 'mensal'],
        ],
    ]));

    $property = Property::first();
    $comparablePrices = $property->prices()->whereHas('priceType', fn ($query) => $query->where('comparable', true))->get();

    expect($comparablePrices)->toHaveCount(1);
    expect($comparablePrices->first()->price_type_id)->toBe($comparableType->id);
});

test('a property can be created with a custom slug for its public URL', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'slug' => 'apartamento-vista-mar-personalizado',
    ]))->assertRedirect(route('properties.index'));

    expect(Property::first()->slug)->toBe('apartamento-vista-mar-personalizado');
});

test('a custom property slug must be unique within the company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    Property::factory()->for($owner->company)->create(['slug' => 'imovel-existente']);

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'slug' => 'imovel-existente',
    ]))->assertInvalid(['slug']);
});

test('a property slug must be lowercase letters, numbers and hyphens', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('properties.store'), validPropertyPayload($priceType->id, [
        'slug' => 'Slug Inválido!',
    ]))->assertInvalid(['slug']);
});

test('keeping the same slug on update does not collide with itself', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    $property = Property::factory()->for($owner->company)->create(['slug' => 'meu-imovel']);

    $this->actingAs($owner)->put(route('properties.update', $property), validPropertyPayload($priceType->id, [
        'slug' => 'meu-imovel',
    ]))->assertSessionHasNoErrors();
});

test('clearing a property slug on update regenerates it from the title', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $priceType = PriceType::factory()->for($owner->company)->create();
    $property = Property::factory()->for($owner->company)->create(['slug' => 'slug-antigo']);

    $this->actingAs($owner)->put(route('properties.update', $property), validPropertyPayload($priceType->id, [
        'title' => 'Novo Titulo Do Imovel',
        'slug' => '',
    ]))->assertRedirect(route('properties.index'));

    expect($property->fresh()->slug)->toBe('novo-titulo-do-imovel');
});
