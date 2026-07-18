<?php

use App\Models\Company;
use App\Models\Property;
use App\Models\TransactionCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingTransactionCategoryAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('a new company is provisioned with the default financial categories', function () {
    $company = Company::factory()->create();

    expect($company->transactionCategories()->count())->toBe(7);
    expect($company->transactionCategories()->where('name', 'IPTU')->where('type', 'despesa')->exists())->toBeTrue();
    expect($company->transactionCategories()->where('name', 'Aluguel')->where('type', 'receita')->exists())->toBeTrue();
});

test('an owner can view, create, edit and delete a transaction category', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('transaction-categories.index'))->assertOk();
    $this->actingAs($owner)->get(route('transaction-categories.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('transaction-categories.store'), [
        'name' => 'Reforma',
        'type' => 'despesa',
    ]);
    $response->assertRedirect(route('transaction-categories.index'));

    $category = TransactionCategory::where('company_id', $owner->company_id)->where('name', 'Reforma')->first();
    expect($category)->not->toBeNull();
    expect($category->type->value)->toBe('despesa');

    $this->actingAs($owner)->get(route('transaction-categories.edit', $category))->assertOk();

    $this->actingAs($owner)->put(route('transaction-categories.update', $category), [
        'name' => 'Reforma emergencial',
        'type' => 'despesa',
    ])->assertRedirect(route('transaction-categories.index'));

    $category->refresh();
    expect($category->name)->toBe('Reforma emergencial');

    $this->actingAs($owner)->delete(route('transaction-categories.destroy', $category))
        ->assertRedirect(route('transaction-categories.index'));
    expect(TransactionCategory::find($category->id))->toBeNull();
});

test('a transaction category cannot be created with a duplicate name in the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('transaction-categories.store'), [
        'name' => 'Aluguel',
        'type' => 'receita',
    ])->assertInvalid(['name']);
});

test('a transaction category linked to a transaction cannot be deleted', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $category = TransactionCategory::factory()->for($owner->company)->create();
    $property = Property::factory()->for($owner->company)->create();

    $owner->company->transactions()->create([
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
        'description' => 'Lançamento teste',
        'amount' => 100,
        'due_date' => now(),
    ]);

    $this->actingAs($owner)->delete(route('transaction-categories.destroy', $category))
        ->assertStatus(422);
    expect(TransactionCategory::find($category->id))->not->toBeNull();
});

test('a user without permission cannot access any transaction category management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $category = TransactionCategory::factory()->for($company)->create();

    $this->actingAs($member)->get(route('transaction-categories.index'))->assertForbidden();
    $this->actingAs($member)->get(route('transaction-categories.create'))->assertForbidden();
    $this->actingAs($member)->post(route('transaction-categories.store'), ['name' => 'Novo', 'type' => 'despesa'])->assertForbidden();
    $this->actingAs($member)->get(route('transaction-categories.edit', $category))->assertForbidden();
    $this->actingAs($member)->put(route('transaction-categories.update', $category), ['name' => 'Hacked', 'type' => 'despesa'])->assertForbidden();
    $this->actingAs($member)->delete(route('transaction-categories.destroy', $category))->assertForbidden();
});

test('a company administrator cannot edit a transaction category from another company', function () {
    $admin = actingTransactionCategoryAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = TransactionCategory::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('transaction-categories.edit', $otherCategory))->assertForbidden();
});

test('a company administrator cannot delete a transaction category from another company', function () {
    $admin = actingTransactionCategoryAdministrator();
    $otherCompany = Company::factory()->create();
    $otherCategory = TransactionCategory::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('transaction-categories.destroy', $otherCategory))->assertForbidden();
    expect($otherCategory->fresh())->not->toBeNull();
});

test('a company administrator never sees transaction categories from another company in the index', function () {
    $admin = actingTransactionCategoryAdministrator();

    $otherCompany = Company::factory()->create();

    $this->actingAs($admin)->get(route('transaction-categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('transaction-categories/index')
            ->has('transactionCategories', 7)
        );
});
