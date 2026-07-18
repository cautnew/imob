<?php

use App\Models\Company;

test('a slug is generated from the name on creation', function () {
    $company = Company::factory()->create(['name' => 'Imobiliária Confiança']);

    expect($company->slug)->toBe('imobiliaria-confianca');
});

test('duplicate names get a suffixed slug', function () {
    $first = Company::factory()->create(['name' => 'Imob Prime']);
    $second = Company::factory()->create(['name' => 'Imob Prime']);

    expect($first->slug)->toBe('imob-prime');
    expect($second->slug)->toBe('imob-prime-2');
});

test('a name colliding with a reserved route segment gets suffixed', function () {
    $company = Company::factory()->create(['name' => 'Dashboard']);

    expect($company->slug)->not->toBe('dashboard');
    expect($company->slug)->toBe('dashboard-2');
});
