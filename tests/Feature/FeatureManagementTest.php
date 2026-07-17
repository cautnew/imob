<?php

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingFeatureAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view, create, edit, toggle and delete a feature', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $category = FeatureCategory::factory()->for($owner->company)->create();

    $this->actingAs($owner)->get(route('features.index'))->assertOk();
    $this->actingAs($owner)->get(route('features.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('features.store'), [
        'name' => 'Piscina',
        'feature_category_id' => $category->id,
    ]);
    $response->assertRedirect(route('features.index'));

    $feature = Feature::where('company_id', $owner->company_id)->where('name', 'Piscina')->first();
    expect($feature)->not->toBeNull();
    expect($feature->active)->toBeTrue();
    expect($feature->feature_category_id)->toBe($category->id);

    $this->actingAs($owner)->get(route('features.edit', $feature))->assertOk();

    $otherCategory = FeatureCategory::factory()->for($owner->company)->create();

    $this->actingAs($owner)->put(route('features.update', $feature), [
        'name' => 'Piscina aquecida',
        'feature_category_id' => $otherCategory->id,
    ])->assertRedirect(route('features.index'));

    expect($feature->fresh()->name)->toBe('Piscina aquecida');
    expect($feature->fresh()->feature_category_id)->toBe($otherCategory->id);

    $this->actingAs($owner)->patch(route('features.toggle', $feature))
        ->assertRedirect(route('features.index'));
    expect($feature->fresh()->active)->toBeFalse();

    $this->actingAs($owner)->patch(route('features.toggle', $feature))
        ->assertRedirect(route('features.index'));
    expect($feature->fresh()->active)->toBeTrue();

    $this->actingAs($owner)->delete(route('features.destroy', $feature))
        ->assertRedirect(route('features.index'));
    expect(Feature::find($feature->id))->toBeNull();
});

test('a feature cannot be created with a category from another company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCategory = FeatureCategory::factory()->for(Company::factory()->create())->create();

    $this->actingAs($owner)->post(route('features.store'), [
        'name' => 'Piscina',
        'feature_category_id' => $otherCategory->id,
    ])->assertInvalid(['feature_category_id']);

    expect(Feature::where('name', 'Piscina')->exists())->toBeFalse();
});

test('a user without permission cannot access any feature management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $category = FeatureCategory::factory()->for($company)->create();
    $feature = Feature::factory()->for($company)->for($category)->create();

    $this->actingAs($member)->get(route('features.index'))->assertForbidden();
    $this->actingAs($member)->get(route('features.create'))->assertForbidden();
    $this->actingAs($member)->post(route('features.store'), ['name' => 'Novo', 'feature_category_id' => $category->id])->assertForbidden();
    $this->actingAs($member)->get(route('features.edit', $feature))->assertForbidden();
    $this->actingAs($member)->put(route('features.update', $feature), ['name' => 'Hacked', 'feature_category_id' => $category->id])->assertForbidden();
    $this->actingAs($member)->patch(route('features.toggle', $feature))->assertForbidden();
    $this->actingAs($member)->delete(route('features.destroy', $feature))->assertForbidden();
});

test('a company administrator cannot edit a feature from another company', function () {
    $admin = actingFeatureAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = FeatureCategory::factory()->for($otherCompany)->create();
    $otherFeature = Feature::factory()->for($otherCompany)->for($otherCategory)->create();

    $this->actingAs($admin)->get(route('features.edit', $otherFeature))->assertForbidden();
});

test('a company administrator cannot update a feature from another company', function () {
    $admin = actingFeatureAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = FeatureCategory::factory()->for($otherCompany)->create();
    $otherFeature = Feature::factory()->for($otherCompany)->for($otherCategory)->create();

    $this->actingAs($admin)->put(route('features.update', $otherFeature), ['name' => 'Hacked', 'feature_category_id' => $otherCategory->id])->assertForbidden();
    expect($otherFeature->fresh()->name)->toBe($otherFeature->name);
});

test('a company administrator cannot toggle a feature from another company', function () {
    $admin = actingFeatureAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = FeatureCategory::factory()->for($otherCompany)->create();
    $otherFeature = Feature::factory()->for($otherCompany)->for($otherCategory)->create();

    $this->actingAs($admin)->patch(route('features.toggle', $otherFeature))->assertForbidden();
    expect($otherFeature->fresh()->active)->toBeTrue();
});

test('a company administrator cannot delete a feature from another company', function () {
    $admin = actingFeatureAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = FeatureCategory::factory()->for($otherCompany)->create();
    $otherFeature = Feature::factory()->for($otherCompany)->for($otherCategory)->create();

    $this->actingAs($admin)->delete(route('features.destroy', $otherFeature))->assertForbidden();
    expect($otherFeature->fresh())->not->toBeNull();
});

test('a company administrator never sees features from another company in the index', function () {
    $admin = actingFeatureAdministrator();
    $category = FeatureCategory::factory()->for($admin->company)->create();
    Feature::factory()->for($admin->company)->for($category)->create();

    $otherCompany = Company::factory()->create();
    $otherCategory = FeatureCategory::factory()->for($otherCompany)->create();
    Feature::factory()->for($otherCompany)->for($otherCategory)->create();

    $this->actingAs($admin)->get(route('features.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/index')
            ->has('features', 1)
            ->where('features.0.company_id', $admin->company_id)
        );
});

test('the index can be filtered by feature category', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $categoryA = FeatureCategory::factory()->for($owner->company)->create();
    $categoryB = FeatureCategory::factory()->for($owner->company)->create();
    Feature::factory()->for($owner->company)->for($categoryA)->create(['name' => 'Piscina']);
    Feature::factory()->for($owner->company)->for($categoryB)->create(['name' => 'Churrasqueira']);

    $this->actingAs($owner)->get(route('features.index', ['feature_category_id' => $categoryA->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/index')
            ->has('features', 1)
            ->where('features.0.name', 'Piscina')
            ->where('selectedCategoryId', $categoryA->id)
        );

    $this->actingAs($owner)->get(route('features.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/index')
            ->has('features', 2)
            ->where('selectedCategoryId', null)
        );
});

test('the create form preselects the category passed via query string', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $category = FeatureCategory::factory()->for($owner->company)->create();

    $this->actingAs($owner)->get(route('features.create', ['feature_category_id' => $category->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/create')
            ->where('selectedCategoryId', $category->id)
        );
});

test('two companies can each have a feature with the same name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $categoryA = FeatureCategory::factory()->for($companyA)->create();
    $categoryB = FeatureCategory::factory()->for($companyB)->create();

    $featureA = Feature::factory()->for($companyA)->for($categoryA)->create(['name' => 'Piscina']);
    $featureB = Feature::factory()->for($companyB)->for($categoryB)->create(['name' => 'Piscina']);

    expect($featureA->id)->not->toBe($featureB->id);
    expect($featureA->name)->toBe($featureB->name);
});
