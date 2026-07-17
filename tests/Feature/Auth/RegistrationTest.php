<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Laravel\Fortify\Features;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

    app(PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);
    expect($user->hasRole('Administrador'))->toBeTrue();
});

test('a new company is provisioned with the default roles', function () {
    $this->seed(PermissionSeeder::class);

    $this->post(route('register.store'), [
        'company_name' => 'Imobiliária Teste',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    app(PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);

    expect($user->can('usuarios.visualizar'))->toBeTrue();

    $roleNames = Role::where('company_id', $user->company_id)->pluck('name');
    expect($roleNames->sort()->values()->all())->toBe([
        'Administrador',
        'Atendente',
        'Corretor',
        'Financeiro',
        'Inquilino',
        'Proprietário',
    ]);

    $admin = Role::where('company_id', $user->company_id)->where('name', 'Administrador')->first();
    expect($admin->permissions()->count())->toBe(count(PermissionSeeder::PERMISSIONS));

    $corretor = Role::where('company_id', $user->company_id)->where('name', 'Corretor')->first();
    expect($corretor->permissions()->count())->toBe(0);
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
