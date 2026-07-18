<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an owner can compare up to four of their own properties', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $category = FeatureCategory::factory()->for($owner->company)->create();
    $pool = Feature::factory()->for($owner->company)->create(['feature_category_id' => $category->id, 'name' => 'Piscina']);

    $bedrooms = PropertyAttribute::factory()->for($owner->company)->create(['name' => 'Quartos', 'type' => 'inteiro']);
    $priceType = PriceType::factory()->for($owner->company)->create(['name' => 'Venda']);

    $withPool = Property::factory()->for($owner->company)->create(['title' => 'Casa com piscina']);
    $withPool->features()->attach($pool);
    $withPool->attributeValues()->create(['property_attribute_id' => $bedrooms->id, 'value' => '3']);
    $withPool->prices()->create(['price_type_id' => $priceType->id, 'amount' => 500000, 'frequency' => 'unico']);

    $withoutPool = Property::factory()->for($owner->company)->create(['title' => 'Casa sem piscina']);
    $withoutPool->attributeValues()->create(['property_attribute_id' => $bedrooms->id, 'value' => '2']);

    $response = $this->actingAs($owner)->get(route('properties.compare', ['ids' => [$withPool->id, $withoutPool->id]]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('properties/compare')
        ->has('properties', 2)
        ->where('properties.0.title', 'Casa com piscina')
        ->where('properties.1.title', 'Casa sem piscina')
        ->has('features', 1)
        ->where('features.0.name', 'Piscina')
        ->has('attributes', 1)
        ->where('attributes.0.name', 'Quartos')
        ->has('priceTypes', 1)
        ->where('maxProperties', 4)
    );
});

test('the comparison is capped at four properties even if more ids are sent', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $properties = Property::factory()->for($owner->company)->count(5)->create();

    $response = $this->actingAs($owner)->get(route('properties.compare', [
        'ids' => $properties->pluck('id')->all(),
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('properties/compare')
        ->has('properties', 4)
    );
});

test('a company cannot compare a property belonging to another company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $ownProperty = Property::factory()->for($owner->company)->create();

    $otherCompany = Company::factory()->create();
    $foreignProperty = Property::factory()->for($otherCompany)->create();

    $response = $this->actingAs($owner)->get(route('properties.compare', [
        'ids' => [$ownProperty->id, $foreignProperty->id],
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('properties/compare')
        ->has('properties', 1)
        ->where('properties.0.id', $ownProperty->id)
    );
});

test('a user without permission cannot access the comparison page', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('properties.compare'))->assertForbidden();
});

test('an empty selection renders an empty comparison page', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $response = $this->actingAs($owner)->get(route('properties.compare'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('properties/compare')
        ->has('properties', 0)
    );
});
