<?php

use App\Models\Company;
use App\Models\Property;

test('a public, available property is shown with valid JSON-LD', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'title' => 'Casa Publica',
        'is_public' => true,
        'status' => 'disponivel',
    ]);

    $response = $this->get(route('public.properties.show', [$company->slug, $property->slug]));

    $response->assertOk();
    $response->assertSee('Casa Publica');

    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches);
    expect($matches)->toHaveCount(2);

    $jsonLd = json_decode($matches[1], true);
    expect($jsonLd)->not->toBeNull();
    expect($jsonLd['@type'])->toBe('Product');
    expect($jsonLd['name'])->toBe('Casa Publica');
});

test('property page shows share links for whatsapp, facebook, telegram, x and a copy link button', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'title' => 'Casa Publica',
        'is_public' => true,
        'status' => 'disponivel',
    ]);

    $response = $this->get(route('public.properties.show', [$company->slug, $property->slug]));

    $shareUrl = route('public.properties.show', [$company->slug, $property->slug]);

    $response->assertOk();
    $response->assertSee('https://wa.me/?text=', false);
    $response->assertSee('https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl), false);
    $response->assertSee('https://t.me/share/url?url='.rawurlencode($shareUrl), false);
    $response->assertSee('https://twitter.com/intent/tweet?url='.rawurlencode($shareUrl), false);
    $response->assertSee('data-copy-url="'.$shareUrl.'"', false);
});

test('a non-public property 404s', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'is_public' => false,
        'status' => 'disponivel',
    ]);

    $this->get(route('public.properties.show', [$company->slug, $property->slug]))->assertNotFound();
});

test('a sold property 404s even if is_public is true', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'is_public' => true,
        'status' => 'vendido',
    ]);

    $this->get(route('public.properties.show', [$company->slug, $property->slug]))->assertNotFound();
});

test('a reserved property is still shown', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'is_public' => true,
        'status' => 'reservado',
    ]);

    $this->get(route('public.properties.show', [$company->slug, $property->slug]))->assertOk();
});
