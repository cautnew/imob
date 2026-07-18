<?php

use App\Models\Company;
use App\Models\Property;

test('the sitemap includes public properties across companies and excludes private ones', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $publicA = Property::factory()->for($companyA)->create(['is_public' => true, 'status' => 'disponivel']);
    $privateA = Property::factory()->for($companyA)->create(['is_public' => false, 'status' => 'disponivel']);
    $publicB = Property::factory()->for($companyB)->create(['is_public' => true, 'status' => 'disponivel']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');

    $body = $response->getContent();
    expect($body)->toContain("/{$companyA->slug}/imoveis/{$publicA->slug}");
    expect($body)->toContain("/{$companyB->slug}/imoveis/{$publicB->slug}");
    expect($body)->not->toContain("/{$companyA->slug}/imoveis/{$privateA->slug}");
    expect($body)->toContain("/{$companyA->slug}<");
    expect($body)->toContain("/{$companyB->slug}<");
});
