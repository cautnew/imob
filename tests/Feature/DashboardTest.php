<?php

use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the dashboard shows the financial summary for the selected month', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $incomeCategory = TransactionCategory::factory()->for($owner->company)->income()->create();
    $expenseCategory = TransactionCategory::factory()->for($owner->company)->expense()->create();

    Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $incomeCategory->id,
        'amount' => '2000.00',
        'due_date' => '2026-07-05',
    ]);

    Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $expenseCategory->id,
        'amount' => '300.00',
        'due_date' => '2026-07-12',
    ]);

    Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $property->id,
        'transaction_category_id' => $expenseCategory->id,
        'amount' => '150.00',
        'due_date' => '2026-05-10',
    ]);

    $this->actingAs($owner)->get(route('dashboard', ['month' => '2026-07']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('selectedMonth', '2026-07')
            ->where('summary.total_income', '2000.00')
            ->where('summary.total_expense', '300.00')
            ->where('summary.balance', '1700.00')
            ->has('monthlySeries', 6)
            ->has('categoryBreakdown')
            ->has('upcoming')
        );
});

test('a user from another company does not see other companies financial data', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherOwner = User::factory()->create(['is_owner' => true]);

    $otherProperty = Property::factory()->for($otherOwner->company)->create();
    $otherCategory = TransactionCategory::factory()->for($otherOwner->company)->income()->create();

    Transaction::factory()->create([
        'company_id' => $otherOwner->company_id,
        'property_id' => $otherProperty->id,
        'transaction_category_id' => $otherCategory->id,
        'amount' => '9999.00',
    ]);

    $this->actingAs($owner)->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('summary.total_income', '0.00')
        );
});
