<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\Property;

test('adding a property to comparison sets a cookie and the comparison page reflects it', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create([
        'title' => 'Casa Comparada', 'is_public' => true, 'status' => 'disponivel',
    ]);

    $storeResponse = $this->post(route('public.comparison.store', [$company->slug, $property->slug]));
    $storeResponse->assertRedirect();

    $cookieValue = $storeResponse->getCookie('portal_comparison')->getValue();
    $decoded = json_decode($cookieValue, true);
    expect($decoded[$company->slug])->toContain($property->id);

    $indexResponse = $this->withCookie('portal_comparison', $cookieValue)
        ->get(route('public.comparison.index', $company->slug));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Casa Comparada');
});

test('the comparison list is capped at the configured maximum', function () {
    $company = Company::factory()->create();
    $properties = Property::factory()->for($company)->count(5)->create([
        'is_public' => true, 'status' => 'disponivel',
    ]);

    $max = config('public-portal.comparison_max');
    $cookie = json_encode([
        $company->slug => $properties->take($max)->pluck('id')->all(),
    ]);

    $fifthProperty = $properties->last();

    $response = $this->withCookie('portal_comparison', $cookie)
        ->post(route('public.comparison.store', [$company->slug, $fifthProperty->slug]));

    $response->assertRedirect();
    $response->assertSessionHas('comparison_error');

    // No new comparison cookie should have been queued — the existing selection stays untouched.
    expect($response->getCookie('portal_comparison', false))->toBeNull();
});

test('the comparison table lists características and highlights differing rows', function () {
    $company = Company::factory()->create();
    $category = FeatureCategory::factory()->for($company)->create();
    $pool = Feature::factory()->for($company)->create(['feature_category_id' => $category->id, 'name' => 'Piscina']);

    $withPool = Property::factory()->for($company)->create([
        'title' => 'Casa com piscina', 'is_public' => true, 'status' => 'disponivel', 'city' => 'Recife',
    ]);
    $withPool->features()->attach($pool);

    $withoutPool = Property::factory()->for($company)->create([
        'title' => 'Casa sem piscina', 'is_public' => true, 'status' => 'disponivel', 'city' => 'Olinda',
    ]);

    $cookie = json_encode([
        $company->slug => [$withPool->id, $withoutPool->id],
    ]);

    $response = $this->withCookie('portal_comparison', $cookie)
        ->get(route('public.comparison.index', $company->slug));

    $response->assertOk();
    $response->assertSee('Piscina');
    $response->assertSee('Sim');
    $response->assertSee('Não');
    $response->assertSee('row-diff', false);
});
