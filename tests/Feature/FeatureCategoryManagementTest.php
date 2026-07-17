<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingFeatureCategoryAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view, create, edit, toggle and delete a feature category', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('feature-categories.index'))->assertOk();
    $this->actingAs($owner)->get(route('feature-categories.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('feature-categories.store'), [
        'name' => 'Área de lazer',
    ]);
    $response->assertRedirect(route('feature-categories.index'));

    $category = FeatureCategory::where('company_id', $owner->company_id)->where('name', 'Área de lazer')->first();
    expect($category)->not->toBeNull();
    expect($category->active)->toBeTrue();

    $this->actingAs($owner)->get(route('feature-categories.edit', $category))->assertOk();

    $this->actingAs($owner)->put(route('feature-categories.update', $category), [
        'name' => 'Área de lazer atualizada',
    ])->assertRedirect(route('feature-categories.index'));

    expect($category->fresh()->name)->toBe('Área de lazer atualizada');

    $this->actingAs($owner)->patch(route('feature-categories.toggle', $category))
        ->assertRedirect(route('feature-categories.index'));
    expect($category->fresh()->active)->toBeFalse();

    $this->actingAs($owner)->patch(route('feature-categories.toggle', $category))
        ->assertRedirect(route('feature-categories.index'));
    expect($category->fresh()->active)->toBeTrue();

    $this->actingAs($owner)->delete(route('feature-categories.destroy', $category))
        ->assertRedirect(route('feature-categories.index'));
    expect(FeatureCategory::find($category->id))->toBeNull();
});

test('a user without permission cannot access any feature category management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $category = FeatureCategory::factory()->for($company)->create();

    $this->actingAs($member)->get(route('feature-categories.index'))->assertForbidden();
    $this->actingAs($member)->get(route('feature-categories.create'))->assertForbidden();
    $this->actingAs($member)->post(route('feature-categories.store'), ['name' => 'Novo'])->assertForbidden();
    $this->actingAs($member)->get(route('feature-categories.edit', $category))->assertForbidden();
    $this->actingAs($member)->put(route('feature-categories.update', $category), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($member)->patch(route('feature-categories.toggle', $category))->assertForbidden();
    $this->actingAs($member)->delete(route('feature-categories.destroy', $category))->assertForbidden();
});

test('a company administrator cannot edit a feature category from another company', function () {
    $admin = actingFeatureCategoryAdministrator();
    $otherCategory = FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($admin)->get(route('feature-categories.edit', $otherCategory))->assertForbidden();
});

test('a company administrator cannot update a feature category from another company', function () {
    $admin = actingFeatureCategoryAdministrator();
    $otherCategory = FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($admin)->put(route('feature-categories.update', $otherCategory), ['name' => 'Hacked'])->assertForbidden();
    expect($otherCategory->fresh()->name)->toBe($otherCategory->name);
});

test('a company administrator cannot toggle a feature category from another company', function () {
    $admin = actingFeatureCategoryAdministrator();
    $otherCategory = FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($admin)->patch(route('feature-categories.toggle', $otherCategory))->assertForbidden();
    expect($otherCategory->fresh()->active)->toBeTrue();
});

test('a company administrator cannot delete a feature category from another company', function () {
    $admin = actingFeatureCategoryAdministrator();
    $otherCategory = FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($admin)->delete(route('feature-categories.destroy', $otherCategory))->assertForbidden();
    expect($otherCategory->fresh())->not->toBeNull();
});

test('a company administrator never sees feature categories from another company in the index', function () {
    $admin = actingFeatureCategoryAdministrator();
    FeatureCategory::factory()->for($admin->company)->create();
    FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($admin)->get(route('feature-categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('feature-categories/index')
            ->has('featureCategories', 1)
            ->where('featureCategories.0.company_id', $admin->company_id)
        );
});

test('two companies can each have a feature category with the same name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $categoryA = FeatureCategory::factory()->for($companyA)->create(['name' => 'Segurança']);
    $categoryB = FeatureCategory::factory()->for($companyB)->create(['name' => 'Segurança']);

    expect($categoryA->id)->not->toBe($categoryB->id);
    expect($categoryA->name)->toBe($categoryB->name);
});

test('a feature category that still has features cannot be deleted', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $category = FeatureCategory::factory()->for($owner->company)->create();
    Feature::factory()->for($owner->company)->for($category)->create();

    $this->actingAs($owner)->delete(route('feature-categories.destroy', $category))->assertStatus(422);
    expect(FeatureCategory::find($category->id))->not->toBeNull();
});
