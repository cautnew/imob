<?php

use App\Models\Company;
use App\Models\Property;

test('a listing never leaks another companys public properties', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Property::factory()->for($companyA)->create(['title' => 'Imovel A', 'is_public' => true, 'status' => 'disponivel']);
    Property::factory()->for($companyB)->create(['title' => 'Imovel B', 'is_public' => true, 'status' => 'disponivel']);

    $response = $this->get(route('public.properties.index', $companyA->slug));

    $response->assertOk();
    $response->assertSee('Imovel A');
    $response->assertDontSee('Imovel B');
});

test('a property detail lookup never resolves across companies', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $propertyB = Property::factory()->for($companyB)->create(['is_public' => true, 'status' => 'disponivel']);

    $this->get(route('public.properties.show', [$companyA->slug, $propertyB->slug]))->assertNotFound();
});
