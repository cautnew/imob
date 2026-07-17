<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('an owner can view the permissions catalog', function () {
    $this->seed(PermissionSeeder::class);
    $owner = User::factory()->create(['is_owner' => true]);

    $response = $this->actingAs($owner)->get(route('permissions.index'));

    $response->assertOk();
});

test('a user without permission cannot view the permissions catalog', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('permissions.index'))->assertForbidden();
});

test('there are no routes to mutate the permission catalog', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post('/permissoes', ['name' => 'hack.everything'])->assertMethodNotAllowed();
    $this->actingAs($owner)->put('/permissoes/1', ['name' => 'hack.everything'])->assertNotFound();
    $this->actingAs($owner)->delete('/permissoes/1')->assertNotFound();
});

test('the permission catalog is global and shared across companies', function () {
    $this->seed(PermissionSeeder::class);

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyA->id);
    $roleA = Role::where('company_id', $companyA->id)->where('name', 'Administrador')->first();

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyB->id);
    $roleB = Role::where('company_id', $companyB->id)->where('name', 'Administrador')->first();

    $permissionIdsA = $roleA->permissions()->pluck('id')->sort()->values();
    $permissionIdsB = $roleB->permissions()->pluck('id')->sort()->values();

    expect($permissionIdsA->all())->toBe($permissionIdsB->all());
});
