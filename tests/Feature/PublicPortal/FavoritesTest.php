<?php

use App\Models\Company;
use App\Models\Property;

test('favoriting a property sets a cookie and the favorites page reflects it', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'title' => 'Casa Favorita', 'is_public' => true, 'status' => 'disponivel',
    ]);

    $storeResponse = $this->post(route('public.favorites.store', [$company->slug, $property->slug]));
    $storeResponse->assertRedirect();

    $cookieValue = $storeResponse->getCookie('portal_favorites')->getValue();
    $decoded = json_decode($cookieValue, true);
    expect($decoded[$company->slug])->toContain($property->id);

    $indexResponse = $this->withCookie('portal_favorites', $cookieValue)
        ->get(route('public.favorites.index', $company->slug));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Casa Favorita');
});

test('the favorites list is capped at the configured maximum', function () {
    $company = Company::factory()->create();
    $max = config('public-portal.favorites_max');
    $properties = Property::factory()->for($company)->count($max + 1)->create([
        'is_public' => true, 'status' => 'disponivel',
    ]);

    $cookie = json_encode([
        $company->slug => $properties->take($max)->pluck('id')->all(),
    ]);

    $extraProperty = $properties->last();

    $response = $this->withCookie('portal_favorites', $cookie)
        ->post(route('public.favorites.store', [$company->slug, $extraProperty->slug]));

    $response->assertRedirect();
    $response->assertSessionHas('favorites_error');

    // No new favorites cookie should have been queued — the existing selection stays untouched.
    expect($response->getCookie('portal_favorites', false))->toBeNull();
});

test('unfavoriting removes only that property, keeping other companies favorites intact', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $propertyA = Property::factory()->for($companyA)->create(['is_public' => true, 'status' => 'disponivel']);
    $propertyB = Property::factory()->for($companyB)->create(['is_public' => true, 'status' => 'disponivel']);

    $cookie = json_encode([
        $companyA->slug => [$propertyA->id],
        $companyB->slug => [$propertyB->id],
    ]);

    $response = $this->withCookie('portal_favorites', $cookie)
        ->delete(route('public.favorites.destroy', [$companyA->slug, $propertyA->slug]));

    $response->assertRedirect();

    $newCookieValue = $response->getCookie('portal_favorites')->getValue();
    $decoded = json_decode($newCookieValue, true);

    expect($decoded[$companyA->slug])->toBe([]);
    expect($decoded[$companyB->slug])->toContain($propertyB->id);
});
