<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function actingCompanyAdmin(): User
{
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view the users screen', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $response = test()->actingAs($owner)->get(route('users.index'));

    $response->assertOk();
});

test('a non-owner administrator can view the users screen via a real permission grant', function () {
    $this->seed(PermissionSeeder::class);
    $admin = actingCompanyAdmin();

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertOk();
});

test('a user without permission cannot access any user management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $other = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('users.index'))->assertForbidden();
    $this->actingAs($member)->get(route('users.create'))->assertForbidden();
    $this->actingAs($member)->post(route('users.store'), [
        'name' => 'New Person',
        'email' => 'new-person@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();
    $this->actingAs($member)->get(route('users.edit', $other))->assertForbidden();
    $this->actingAs($member)->put(route('users.update', $other), ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
    $this->actingAs($member)->delete(route('users.destroy', $other))->assertForbidden();
});

test('an owner can create a user and assign roles', function () {
    $this->seed(PermissionSeeder::class);
    $owner = User::factory()->create(['is_owner' => true]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->company_id);
    $corretor = Role::where('company_id', $owner->company_id)->where('name', 'Corretor')->first();

    $response = $this->actingAs($owner)->post(route('users.store'), [
        'name' => 'Colleague',
        'email' => 'colleague@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$corretor->id],
    ]);

    $response->assertRedirect(route('users.index'));

    $member = User::where('email', 'colleague@example.com')->first();

    expect($member)->not->toBeNull();
    expect($member->company_id)->toBe($owner->company_id);
    expect($member->is_owner)->toBeFalse();
    expect($member->hasRole('Corretor'))->toBeTrue();
});

test('a company admin cannot view, edit or delete a user from another company', function () {
    $this->seed(PermissionSeeder::class);
    $admin = actingCompanyAdmin();

    $otherCompany = Company::factory()->create();
    $otherUser = User::factory()->for($otherCompany)->create(['is_owner' => false]);

    $this->actingAs($admin)->get(route('users.edit', $otherUser))->assertForbidden();
    $this->actingAs($admin)->put(route('users.update', $otherUser), [
        'name' => 'Hacked',
        'email' => $otherUser->email,
    ])->assertForbidden();
    $this->actingAs($admin)->delete(route('users.destroy', $otherUser))->assertForbidden();

    expect($otherUser->fresh()->name)->not->toBe('Hacked');
});

test('the owner cannot be deleted by anyone', function () {
    $this->seed(PermissionSeeder::class);
    $owner = User::factory()->create(['is_owner' => true]);
    $admin = User::factory()->for($owner->company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->company_id);
    $admin->assignRole('Administrador');

    $this->actingAs($admin)->delete(route('users.destroy', $owner))->assertForbidden();
    expect($owner->fresh())->not->toBeNull();
});

test('a user cannot delete themself', function () {
    $this->seed(PermissionSeeder::class);
    $admin = actingCompanyAdmin();

    $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertForbidden();
    expect($admin->fresh())->not->toBeNull();
});

test('a non-owner user cannot escalate their own roles when editing themself', function () {
    $this->seed(PermissionSeeder::class);
    $admin = actingCompanyAdmin();

    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->company_id);
    $financeiro = Role::where('company_id', $admin->company_id)->where('name', 'Financeiro')->first();

    $response = $this->actingAs($admin)->put(route('users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'roles' => [$financeiro->id],
    ]);

    $response->assertRedirect(route('users.index'));

    expect($admin->fresh()->hasRole('Administrador'))->toBeTrue();
    expect($admin->fresh()->hasRole('Financeiro'))->toBeFalse();
});

test('email must be unique when creating or updating a user', function () {
    $this->seed(PermissionSeeder::class);
    $owner = User::factory()->create(['is_owner' => true]);
    $existing = User::factory()->for($owner->company)->create(['is_owner' => false]);

    $response = $this->actingAs($owner)->post(route('users.store'), [
        'name' => 'Duplicate',
        'email' => $existing->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid(['email']);
});

test('a new member cannot see or access the user management routes without permission', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('dashboard'))->assertOk();
    $this->actingAs($member)->get(route('users.index'))->assertForbidden();
});
