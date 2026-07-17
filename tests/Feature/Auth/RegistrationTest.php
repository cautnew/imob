<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register and a company is created for them', function () {
    $response = $this->post(route('register.store'), [
        'company_name' => 'Imobiliária Teste',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->is_owner)->toBeTrue();
    expect($user->company_id)->not->toBeNull();
    expect(Company::find($user->company_id)->name)->toBe('Imobiliária Teste');
});

test('registration requires a company name', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid(['company_name']);
    $this->assertGuest();
});
