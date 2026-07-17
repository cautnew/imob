<?php

use App\Models\Company;
use App\Models\User;

test('a user with a pending company is redirected from the dashboard to onboarding', function () {
    $company = Company::factory()->pendingOnboarding()->create();
    $user = User::factory()->for($company)->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('onboarding.edit'));
});

test('the onboarding screen can be rendered for a pending company', function () {
    $company = Company::factory()->pendingOnboarding()->create();
    $user = User::factory()->for($company)->create();

    $response = $this->actingAs($user)->get(route('onboarding.edit'));

    $response->assertOk();
});

test('an already onboarded company is redirected away from the onboarding screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('onboarding.edit'));

    $response->assertRedirect(route('dashboard'));
});

test('completing onboarding marks the company as onboarded and unlocks the dashboard', function () {
    $company = Company::factory()->pendingOnboarding()->create();
    $user = User::factory()->for($company)->create();

    $response = $this->actingAs($user)->put(route('onboarding.update'), [
        'document' => '12.345.678/0001-90',
        'phone' => '(11) 99999-0000',
        'address' => 'Rua Teste, 123',
    ]);

    $response->assertRedirect(route('dashboard'));

    expect($company->refresh()->onboarded_at)->not->toBeNull();
    expect($company->document)->toBe('12.345.678/0001-90');

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});
