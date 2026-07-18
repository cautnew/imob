<?php

use App\Enums\TransactionStatus;
use App\Models\Company;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingTransactionAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

/**
 * @return array{property: Property, category: TransactionCategory}
 */
function transactionParties(Company $company): array
{
    return [
        'property' => Property::factory()->for($company)->create(),
        'category' => TransactionCategory::factory()->for($company)->expense()->create(),
    ];
}

/**
 * @return array<string, mixed>
 */
function validTransactionPayload(array $overrides = []): array
{
    return array_merge([
        'description' => 'IPTU de julho',
        'amount' => '450.00',
        'due_date' => '2026-07-10',
        'notes' => 'Referente ao mês de julho',
    ], $overrides);
}

test('an owner can view, create, edit and delete a transaction', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'category' => $category] = transactionParties($owner->company);

    $this->actingAs($owner)->get(route('transactions.index'))->assertOk();
    $this->actingAs($owner)->get(route('transactions.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('transactions.store'), validTransactionPayload([
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
    ]));
    $response->assertRedirect(route('transactions.index'));

    $transaction = Transaction::where('company_id', $owner->company_id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::Pending);

    $this->actingAs($owner)->get(route('transactions.edit', $transaction))->assertOk();

    $this->actingAs($owner)->put(route('transactions.update', $transaction), validTransactionPayload([
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
        'amount' => '500.00',
    ]))->assertRedirect(route('transactions.index'));

    $transaction->refresh();
    expect((float) $transaction->amount)->toBe(500.0);

    $this->actingAs($owner)->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('transactions.index'));
    expect(Transaction::find($transaction->id))->toBeNull();
});

test('required fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('transactions.store'), validTransactionPayload([
        'property_id' => '',
        'transaction_category_id' => '',
        'description' => '',
        'amount' => '',
        'due_date' => '',
    ]))->assertInvalid(['property_id', 'transaction_category_id', 'description', 'amount', 'due_date']);
});

test('property_id and transaction_category_id must belong to the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCompany = Company::factory()->create();
    ['property' => $foreignProperty, 'category' => $foreignCategory] = transactionParties($otherCompany);
    ['property' => $property, 'category' => $category] = transactionParties($owner->company);

    $this->actingAs($owner)->post(route('transactions.store'), validTransactionPayload([
        'property_id' => $foreignProperty->id,
        'transaction_category_id' => $category->id,
    ]))->assertInvalid(['property_id']);

    $this->actingAs($owner)->post(route('transactions.store'), validTransactionPayload([
        'property_id' => $property->id,
        'transaction_category_id' => $foreignCategory->id,
    ]))->assertInvalid(['transaction_category_id']);
});

test('toggling status marks a transaction as paid and back to pending', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'category' => $category] = transactionParties($owner->company);
    $transaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
    ]);

    $this->actingAs($owner)->patch(route('transactions.status.toggle', $transaction))
        ->assertRedirect(route('transactions.index'));

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::Paid);
    expect($transaction->paid_date)->not->toBeNull();

    $this->actingAs($owner)->patch(route('transactions.status.toggle', $transaction))
        ->assertRedirect(route('transactions.index'));

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::Pending);
    expect($transaction->paid_date)->toBeNull();
});

test('a pending transaction past its due date is reported as vencido without being written', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    ['property' => $property, 'category' => $category] = transactionParties($owner->company);
    $transaction = Transaction::factory()->overdue()->create([
        'company_id' => $owner->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
    ]);

    expect($transaction->effectiveStatus())->toBe(TransactionStatus::Overdue);
    expect($transaction->getRawOriginal('status'))->toBe('pendente');

    $this->actingAs($owner)->get(route('transactions.index', ['status' => 'vencido']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('transactions/index')
            ->has('transactions.data', 1)
            ->where('transactions.data.0.status', 'vencido')
        );
});

test('a user without permission cannot access any transaction management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    ['property' => $property, 'category' => $category] = transactionParties($company);
    $transaction = Transaction::factory()->create([
        'company_id' => $company->id,
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
    ]);

    $this->actingAs($member)->get(route('transactions.index'))->assertForbidden();
    $this->actingAs($member)->get(route('transactions.create'))->assertForbidden();
    $this->actingAs($member)->post(route('transactions.store'), validTransactionPayload())->assertForbidden();
    $this->actingAs($member)->get(route('transactions.edit', $transaction))->assertForbidden();
    $this->actingAs($member)->put(route('transactions.update', $transaction), validTransactionPayload())->assertForbidden();
    $this->actingAs($member)->patch(route('transactions.status.toggle', $transaction))->assertForbidden();
    $this->actingAs($member)->delete(route('transactions.destroy', $transaction))->assertForbidden();
});

test('a company administrator never sees transactions from another company in the index', function () {
    $admin = actingTransactionAdministrator();
    ['property' => $property, 'category' => $category] = transactionParties($admin->company);
    Transaction::factory()->create([
        'company_id' => $admin->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $category->id,
    ]);

    $otherCompany = Company::factory()->create();
    ['property' => $otherProperty, 'category' => $otherCategory] = transactionParties($otherCompany);
    Transaction::factory()->create([
        'company_id' => $otherCompany->id,
        'property_id' => $otherProperty->id,
        'transaction_category_id' => $otherCategory->id,
    ]);

    $this->actingAs($admin)->get(route('transactions.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('transactions/index')
            ->has('transactions.data', 1)
        );
});
