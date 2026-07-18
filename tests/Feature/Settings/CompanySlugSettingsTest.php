<?php

use App\Models\Company;
use App\Models\User;

test('the company settings page is displayed to an owner', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('company.edit'))->assertOk();
});

test('a non-owner cannot view or update the company slug', function () {
    $staff = User::factory()->create(['is_owner' => false]);

    $this->actingAs($staff)->get(route('company.edit'))->assertForbidden();

    $this->actingAs($staff)->put(route('company.update'), [
        'slug' => 'nova-imobiliaria',
    ])->assertForbidden();
});

test('an owner can update the company slug', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $response = $this->actingAs($owner)->put(route('company.update'), [
        'slug' => 'minha-imobiliaria-nova',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('company.edit'));

    expect($owner->company->fresh()->slug)->toBe('minha-imobiliaria-nova');
});

test('the slug must be unique across companies', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    Company::factory()->create(['slug' => 'ja-existe']);

    $this->actingAs($owner)->put(route('company.update'), [
        'slug' => 'ja-existe',
    ])->assertInvalid(['slug']);
});

test('the slug format must be lowercase letters, numbers and hyphens', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->put(route('company.update'), [
        'slug' => 'Não Válido!',
    ])->assertInvalid(['slug']);
});

test('the slug cannot collide with a reserved route segment', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->put(route('company.update'), [
        'slug' => 'dashboard',
    ])->assertInvalid(['slug']);
});

test('keeping the same slug on update does not error', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $currentSlug = $owner->company->slug;

    $this->actingAs($owner)->put(route('company.update'), [
        'slug' => $currentSlug,
    ])->assertSessionHasNoErrors();
});
