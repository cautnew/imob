<?php

use App\Models\Company;
use App\Models\PriceType;
use App\Models\Property;

test('for a sale property, the cheapest sale-purposed price type wins', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create(['purpose' => 'venda']);

    $salePriceType = PriceType::factory()->for($company)->create(['purpose' => 'venda']);
    $rentPriceType = PriceType::factory()->for($company)->create(['purpose' => 'aluguel']);

    $property->prices()->create(['price_type_id' => $rentPriceType->id, 'amount' => 2000, 'frequency' => 'mensal']);
    $sale = $property->prices()->create(['price_type_id' => $salePriceType->id, 'amount' => 500000, 'frequency' => 'unico']);

    $property->load('prices.priceType');

    expect($property->principalPrice()->id)->toBe($sale->id);
});

test('for a sale-and-rent property, sale-purposed types are preferred over rent', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create(['purpose' => 'venda_aluguel']);

    $salePriceType = PriceType::factory()->for($company)->create(['purpose' => 'venda']);
    $rentPriceType = PriceType::factory()->for($company)->create(['purpose' => 'aluguel']);

    $rent = $property->prices()->create(['price_type_id' => $rentPriceType->id, 'amount' => 2000, 'frequency' => 'mensal']);
    $sale = $property->prices()->create(['price_type_id' => $salePriceType->id, 'amount' => 500000, 'frequency' => 'unico']);

    $property->load('prices.priceType');

    expect($property->principalPrice()->id)->toBe($sale->id);

    // Removing the sale-purposed price falls back to the rent-purposed one.
    $sale->delete();
    $property->load('prices.priceType');

    expect($property->principalPrice()->id)->toBe($rent->id);
});

test('without any purpose-tagged price type, the cheapest price is used as a fallback', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create(['purpose' => 'venda']);

    $untaggedA = PriceType::factory()->for($company)->create(['purpose' => null]);
    $untaggedB = PriceType::factory()->for($company)->create(['purpose' => null]);

    $property->prices()->create(['price_type_id' => $untaggedA->id, 'amount' => 900, 'frequency' => 'unico']);
    $cheapest = $property->prices()->create(['price_type_id' => $untaggedB->id, 'amount' => 500, 'frequency' => 'unico']);

    $property->load('prices.priceType');

    expect($property->principalPrice()->id)->toBe($cheapest->id);
});

test('withPrincipalPrice matches principalPrice for filtering and sorting', function () {
    $company = Company::factory()->create();
    $priceType = PriceType::factory()->for($company)->create(['purpose' => 'venda']);

    $property = Property::factory()->for($company)->create(['purpose' => 'venda', 'is_public' => true, 'status' => 'disponivel']);
    $property->prices()->create(['price_type_id' => $priceType->id, 'amount' => 250000, 'frequency' => 'unico']);

    $property->load('prices.priceType');

    $viaScope = Property::withPrincipalPrice()->where('id', $property->id)->first();

    expect((float) $viaScope->principal_price)->toBe((float) $property->principalPrice()->amount);
});
