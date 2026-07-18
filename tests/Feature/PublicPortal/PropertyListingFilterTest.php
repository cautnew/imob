<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeOption;

function publicPortalProperty(Company $company, array $overrides = []): Property
{
    return Property::factory()->for($company)->create(array_merge([
        'is_public' => true,
        'status' => 'disponivel',
        'purpose' => 'venda',
    ], $overrides));
}

test('the listing only shows public properties belonging to that company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    publicPortalProperty($company, ['title' => 'Casa Visivel']);
    publicPortalProperty($company, ['title' => 'Casa Privada', 'is_public' => false]);
    publicPortalProperty($company, ['title' => 'Casa Vendida', 'status' => 'vendido']);
    publicPortalProperty($otherCompany, ['title' => 'Casa De Outra Empresa']);

    $response = $this->get(route('public.properties.index', $company->slug));

    $response->assertOk();
    $response->assertSee('Casa Visivel');
    $response->assertDontSee('Casa Privada');
    $response->assertDontSee('Casa Vendida');
    $response->assertDontSee('Casa De Outra Empresa');
});

test('filters by principal price range', function () {
    $company = Company::factory()->create();
    $priceType = PriceType::factory()->for($company)->create(['purpose' => 'venda']);

    $cheap = publicPortalProperty($company, ['title' => 'Casa Barata']);
    $cheap->prices()->create(['price_type_id' => $priceType->id, 'amount' => 100000, 'frequency' => 'unico']);

    $expensive = publicPortalProperty($company, ['title' => 'Casa Cara']);
    $expensive->prices()->create(['price_type_id' => $priceType->id, 'amount' => 900000, 'frequency' => 'unico']);

    $response = $this->get(route('public.properties.index', $company->slug).'?preco_max=500000');

    $response->assertOk();
    $response->assertSee('Casa Barata');
    $response->assertDontSee('Casa Cara');
});

test('filters by neighborhood', function () {
    $company = Company::factory()->create();

    publicPortalProperty($company, ['title' => 'Casa Centro', 'neighborhood' => 'Centro']);
    publicPortalProperty($company, ['title' => 'Casa Jardim', 'neighborhood' => 'Jardim']);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query(['bairro' => ['Centro']]));

    $response->assertOk();
    $response->assertSee('Casa Centro');
    $response->assertDontSee('Casa Jardim');
});

test('filters by city', function () {
    $company = Company::factory()->create();

    publicPortalProperty($company, ['title' => 'Casa SP', 'city' => 'São Paulo']);
    publicPortalProperty($company, ['title' => 'Casa RJ', 'city' => 'Rio de Janeiro']);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query(['cidade' => ['Rio de Janeiro']]));

    $response->assertOk();
    $response->assertSee('Casa RJ');
    $response->assertDontSee('Casa SP');
});

test('filters by multiple features using AND semantics', function () {
    $company = Company::factory()->create();
    $category = FeatureCategory::factory()->for($company)->create();
    $pool = Feature::factory()->for($company)->create(['feature_category_id' => $category->id, 'name' => 'Piscina']);
    $gym = Feature::factory()->for($company)->create(['feature_category_id' => $category->id, 'name' => 'Academia']);

    $both = publicPortalProperty($company, ['title' => 'Casa Completa']);
    $both->features()->attach([$pool->id, $gym->id]);

    $onlyPool = publicPortalProperty($company, ['title' => 'Casa So Piscina']);
    $onlyPool->features()->attach([$pool->id]);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query([
        'caracteristicas' => [$pool->id, $gym->id],
    ]));

    $response->assertOk();
    $response->assertSee('Casa Completa');
    $response->assertDontSee('Casa So Piscina');
});

test('filters by a filterable integer custom attribute range', function () {
    $company = Company::factory()->create();
    $bedrooms = PropertyAttribute::factory()->for($company)->create([
        'name' => 'Quartos', 'type' => 'inteiro', 'filterable' => true,
    ]);

    $three = publicPortalProperty($company, ['title' => 'Casa 3 Quartos']);
    $three->attributeValues()->create(['property_attribute_id' => $bedrooms->id, 'value' => '3']);

    $five = publicPortalProperty($company, ['title' => 'Casa 5 Quartos']);
    $five->attributeValues()->create(['property_attribute_id' => $bedrooms->id, 'value' => '5']);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query([
        'atributos' => [$bedrooms->id => ['min' => 4, 'max' => 6]],
    ]));

    $response->assertOk();
    $response->assertSee('Casa 5 Quartos');
    $response->assertDontSee('Casa 3 Quartos');
});

test('filters by a filterable boolean custom attribute', function () {
    $company = Company::factory()->create();
    $furnished = PropertyAttribute::factory()->for($company)->create([
        'name' => 'Mobiliado', 'type' => 'boolean', 'filterable' => true,
    ]);

    $yes = publicPortalProperty($company, ['title' => 'Casa Mobiliada']);
    $yes->attributeValues()->create(['property_attribute_id' => $furnished->id, 'value' => '1']);

    $no = publicPortalProperty($company, ['title' => 'Casa Vazia']);
    $no->attributeValues()->create(['property_attribute_id' => $furnished->id, 'value' => '0']);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query([
        'atributos' => [$furnished->id => '1'],
    ]));

    $response->assertOk();
    $response->assertSee('Casa Mobiliada');
    $response->assertDontSee('Casa Vazia');
});

test('filters by a filterable select custom attribute', function () {
    $company = Company::factory()->create();
    $finish = PropertyAttribute::factory()->for($company)->select()->create([
        'name' => 'Padrão de acabamento', 'filterable' => true,
    ]);
    $high = PropertyAttributeOption::factory()->for($finish, 'propertyAttribute')->create(['value' => 'Alto padrão']);
    $standard = PropertyAttributeOption::factory()->for($finish, 'propertyAttribute')->create(['value' => 'Padrão']);

    $highEnd = publicPortalProperty($company, ['title' => 'Casa Alto Padrao']);
    $highEnd->attributeValues()->create(['property_attribute_id' => $finish->id, 'property_attribute_option_id' => $high->id]);

    $standardEnd = publicPortalProperty($company, ['title' => 'Casa Padrao']);
    $standardEnd->attributeValues()->create(['property_attribute_id' => $finish->id, 'property_attribute_option_id' => $standard->id]);

    $response = $this->get(route('public.properties.index', $company->slug).'?'.http_build_query([
        'atributos' => [$finish->id => $high->id],
    ]));

    $response->assertOk();
    $response->assertSee('Casa Alto Padrao');
    $response->assertDontSee('Casa Padrao');
});
