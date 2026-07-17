<?php

use App\Models\Company;
use App\Models\PropertyAttribute;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingPropertyAttributeAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view, create, edit and delete a text property attribute', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('property-attributes.index'))->assertOk();
    $this->actingAs($owner)->get(route('property-attributes.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('property-attributes.store'), [
        'name' => 'Metragem',
        'type' => 'inteiro',
        'filterable' => true,
        'comparable' => true,
        'required' => false,
    ]);
    $response->assertRedirect(route('property-attributes.index'));

    $attribute = PropertyAttribute::where('company_id', $owner->company_id)->where('name', 'Metragem')->first();
    expect($attribute)->not->toBeNull();
    expect($attribute->type->value)->toBe('inteiro');
    expect($attribute->filterable)->toBeTrue();
    expect($attribute->comparable)->toBeTrue();
    expect($attribute->required)->toBeFalse();
    expect($attribute->options)->toHaveCount(0);

    $this->actingAs($owner)->get(route('property-attributes.edit', $attribute))->assertOk();

    $this->actingAs($owner)->put(route('property-attributes.update', $attribute), [
        'name' => 'Metragem total',
        'type' => 'decimal',
        'filterable' => false,
        'comparable' => false,
        'required' => true,
    ])->assertRedirect(route('property-attributes.index'));

    $attribute->refresh();
    expect($attribute->name)->toBe('Metragem total');
    expect($attribute->type->value)->toBe('decimal');
    expect($attribute->filterable)->toBeFalse();
    expect($attribute->required)->toBeTrue();

    $this->actingAs($owner)->delete(route('property-attributes.destroy', $attribute))
        ->assertRedirect(route('property-attributes.index'));
    expect(PropertyAttribute::find($attribute->id))->toBeNull();
});

test('creating a select attribute requires and persists options', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('property-attributes.store'), [
        'name' => 'Padrão de acabamento',
        'type' => 'select',
    ])->assertInvalid(['options']);

    $response = $this->actingAs($owner)->post(route('property-attributes.store'), [
        'name' => 'Padrão de acabamento',
        'type' => 'select',
        'options' => [
            ['value' => 'Simples'],
            ['value' => 'Alto padrão'],
        ],
    ]);
    $response->assertRedirect(route('property-attributes.index'));

    $attribute = PropertyAttribute::where('name', 'Padrão de acabamento')->first();
    expect($attribute->options()->orderBy('order')->pluck('value')->all())->toBe(['Simples', 'Alto padrão']);
});

test('options are rejected for types that do not support them', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('property-attributes.store'), [
        'name' => 'Metragem',
        'type' => 'inteiro',
        'options' => [['value' => 'Não deveria existir']],
    ])->assertInvalid(['options']);

    expect(PropertyAttribute::where('name', 'Metragem')->exists())->toBeFalse();
});

test('updating an attribute replaces its previous options', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $attribute = PropertyAttribute::factory()->select()->for($owner->company)->create();
    $attribute->options()->createMany([
        ['value' => 'Antiga A', 'order' => 0],
        ['value' => 'Antiga B', 'order' => 1],
    ]);

    $this->actingAs($owner)->put(route('property-attributes.update', $attribute), [
        'name' => $attribute->name,
        'type' => 'select',
        'options' => [
            ['value' => 'Nova A'],
        ],
    ])->assertRedirect(route('property-attributes.index'));

    expect($attribute->options()->pluck('value')->all())->toBe(['Nova A']);
});

test('a property attribute cannot be created with a duplicate name in the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    PropertyAttribute::factory()->for($owner->company)->create(['name' => 'Metragem']);

    $this->actingAs($owner)->post(route('property-attributes.store'), [
        'name' => 'Metragem',
        'type' => 'inteiro',
    ])->assertInvalid(['name']);
});

test('a user without permission cannot access any property attribute management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $attribute = PropertyAttribute::factory()->for($company)->create();

    $this->actingAs($member)->get(route('property-attributes.index'))->assertForbidden();
    $this->actingAs($member)->get(route('property-attributes.create'))->assertForbidden();
    $this->actingAs($member)->post(route('property-attributes.store'), ['name' => 'Novo', 'type' => 'texto'])->assertForbidden();
    $this->actingAs($member)->get(route('property-attributes.edit', $attribute))->assertForbidden();
    $this->actingAs($member)->put(route('property-attributes.update', $attribute), ['name' => 'Hacked', 'type' => 'texto'])->assertForbidden();
    $this->actingAs($member)->delete(route('property-attributes.destroy', $attribute))->assertForbidden();
});

test('a company administrator cannot edit a property attribute from another company', function () {
    $admin = actingPropertyAttributeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherAttribute = PropertyAttribute::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('property-attributes.edit', $otherAttribute))->assertForbidden();
});

test('a company administrator cannot update a property attribute from another company', function () {
    $admin = actingPropertyAttributeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherAttribute = PropertyAttribute::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->put(route('property-attributes.update', $otherAttribute), [
        'name' => 'Hacked',
        'type' => 'texto',
    ])->assertForbidden();
    expect($otherAttribute->fresh()->name)->toBe($otherAttribute->name);
});

test('a company administrator cannot delete a property attribute from another company', function () {
    $admin = actingPropertyAttributeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherAttribute = PropertyAttribute::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('property-attributes.destroy', $otherAttribute))->assertForbidden();
    expect($otherAttribute->fresh())->not->toBeNull();
});

test('a company administrator never sees property attributes from another company in the index', function () {
    $admin = actingPropertyAttributeAdministrator();
    PropertyAttribute::factory()->for($admin->company)->create();

    $otherCompany = Company::factory()->create();
    PropertyAttribute::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('property-attributes.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('property-attributes/index')
            ->has('propertyAttributes', 1)
            ->where('propertyAttributes.0.company_id', $admin->company_id)
        );
});

test('two companies can each have a property attribute with the same name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $attributeA = PropertyAttribute::factory()->for($companyA)->create(['name' => 'Metragem']);
    $attributeB = PropertyAttribute::factory()->for($companyB)->create(['name' => 'Metragem']);

    expect($attributeA->id)->not->toBe($attributeB->id);
    expect($attributeA->name)->toBe($attributeB->name);
});
