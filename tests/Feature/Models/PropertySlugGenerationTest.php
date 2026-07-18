<?php

use App\Models\Company;
use App\Models\Property;

test('a slug is generated from the title on creation', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create(['title' => 'Casa Incrível']);

    expect($property->slug)->toBe('casa-incrivel');
});

test('duplicate titles within the same company get a suffixed slug', function () {
    $company = Company::factory()->create();

    $first = Property::factory()->for($company)->create(['title' => 'Casa Padrão']);
    $second = Property::factory()->for($company)->create(['title' => 'Casa Padrão']);

    expect($first->slug)->toBe('casa-padrao');
    expect($second->slug)->toBe('casa-padrao-2');
});

test('the same title in different companies does not collide', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $propertyA = Property::factory()->for($companyA)->create(['title' => 'Casa Repetida']);
    $propertyB = Property::factory()->for($companyB)->create(['title' => 'Casa Repetida']);

    expect($propertyA->slug)->toBe('casa-repetida');
    expect($propertyB->slug)->toBe('casa-repetida');
});

test('an explicit slug is respected', function () {
    $company = Company::factory()->create();
    $property = Property::factory()->for($company)->create(['title' => 'Casa', 'slug' => 'meu-slug-customizado']);

    expect($property->slug)->toBe('meu-slug-customizado');
});
