<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function actingCompanyAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view, create, edit and delete a role', function () {
    $this->seed(PermissionSeeder::class);
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('roles.index'))->assertOk();
    $this->actingAs($owner)->get(route('roles.create'))->assertOk();

    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->company_id);
    $permission = Permission::where('name', 'usuarios.visualizar')->first();

    $response = $this->actingAs($owner)->post(route('roles.store'), [
        'name' => 'Corretor Sênior',
        'permissions' => [$permission->id],
    ]);
    $response->assertRedirect(route('roles.index'));

    $role = Role::where('company_id', $owner->company_id)->where('name', 'Corretor Sênior')->first();
    expect($role)->not->toBeNull();
    expect($role->permissions()->pluck('name')->all())->toBe(['usuarios.visualizar']);

    $this->actingAs($owner)->get(route('roles.edit', $role))->assertOk();

    $this->actingAs($owner)->put(route('roles.update', $role), [
        'name' => 'Corretor Sênior Atualizado',
        'permissions' => [],
    ])->assertRedirect(route('roles.index'));

    expect($role->fresh()->name)->toBe('Corretor Sênior Atualizado');
    expect($role->fresh()->permissions()->count())->toBe(0);

    $this->actingAs($owner)->delete(route('roles.destroy', $role))->assertRedirect(route('roles.index'));
    expect(Role::find($role->id))->toBeNull();
});

test('a user without permission cannot access any role management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $role = Role::where('company_id', $company->id)->where('name', 'Corretor')->first();

    $this->actingAs($member)->get(route('roles.index'))->assertForbidden();
    $this->actingAs($member)->get(route('roles.create'))->assertForbidden();
    $this->actingAs($member)->post(route('roles.store'), ['name' => 'Novo'])->assertForbidden();
    $this->actingAs($member)->get(route('roles.edit', $role))->assertForbidden();
    $this->actingAs($member)->put(route('roles.update', $role), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($member)->delete(route('roles.destroy', $role))->assertForbidden();
});

test('a company administrator cannot view edit or delete a role from another company', function () {
    $admin = actingCompanyAdministrator();

    $otherCompany = Company::factory()->create();
    app(PermissionRegistrar::class)->setPermissionsTeamId($otherCompany->id);
    $otherRole = Role::where('company_id', $otherCompany->id)->where('name', 'Corretor')->first();

    $this->actingAs($admin)->get(route('roles.edit', $otherRole))->assertForbidden();
    $this->actingAs($admin)->put(route('roles.update', $otherRole), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($admin)->delete(route('roles.destroy', $otherRole))->assertForbidden();

    expect($otherRole->fresh()->name)->toBe('Corretor');
});

test('a company administrator never sees roles from another company in the index', function () {
    $admin = actingCompanyAdministrator();
    Company::factory()->create();

    $this->actingAs($admin)->get(route('roles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('roles/index')
            ->has('roles', 6)
            ->where('roles.0.company_id', $admin->company_id)
        );
});

test('two companies can each have a role with the same name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyA->id);
    $roleA = Role::where('company_id', $companyA->id)->where('name', 'Corretor')->first();

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyB->id);
    $roleB = Role::where('company_id', $companyB->id)->where('name', 'Corretor')->first();

    expect($roleA->id)->not->toBe($roleB->id);
    expect($roleA->name)->toBe($roleB->name);
});

test('a role that still has users assigned cannot be deleted', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->company_id);
    $corretor = Role::where('company_id', $owner->company_id)->where('name', 'Corretor')->first();

    $member = User::factory()->for($owner->company)->create(['is_owner' => false]);
    $member->assignRole('Corretor');

    $this->actingAs($owner)->delete(route('roles.destroy', $corretor))->assertStatus(422);
    expect(Role::find($corretor->id))->not->toBeNull();
});
