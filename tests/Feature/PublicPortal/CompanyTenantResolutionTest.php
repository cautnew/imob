<?php

use App\Models\Company;

test('an unknown company slug 404s', function () {
    $this->get('/empresa-que-nao-existe')->assertNotFound();

    expect(app()->bound('currentCompany'))->toBeFalse();
});

test('a known company slug resolves and binds currentCompany', function () {
    $company = Company::factory()->create();

    $this->get("/{$company->slug}")->assertOk();

    expect(app()->bound('currentCompany'))->toBeTrue();
    expect(app('currentCompany')->id)->toBe($company->id);
});

test('currentCompany is not bound outside the public portal group', function () {
    $this->get(route('home'))->assertOk();

    expect(app()->bound('currentCompany'))->toBeFalse();
});
