<?php

use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;

function billTransactionLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

test('an owner can attach an existing lançamento from the same lease and it composes the total', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billTransactionLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $transaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'lease_id' => $lease->id,
        'transaction_category_id' => $category->id,
        'amount' => '350.00',
    ]);

    $this->actingAs($owner)->post(route('bill-transactions.store', $bill), [
        'transaction_id' => $transaction->id,
    ])->assertRedirect();

    $transaction->refresh();
    expect($transaction->bill_id)->toBe($bill->id);
    expect($bill->totalAmount())->toBe('350.00');
    expect($bill->events()->where('type', 'lancamento_vinculado')->exists())->toBeTrue();
});

test('a lançamento from a different lease, another company, or already linked cannot be attached', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billTransactionLeaseForCompany($owner->company);
    $otherLease = billTransactionLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $otherBill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $wrongLeaseTransaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $otherLease->property_id,
        'lease_id' => $otherLease->id,
        'transaction_category_id' => $category->id,
    ]);

    $this->actingAs($owner)->post(route('bill-transactions.store', $bill), [
        'transaction_id' => $wrongLeaseTransaction->id,
    ])->assertInvalid(['transaction_id']);

    $otherCompany = Company::factory()->create();
    $foreignCategory = TransactionCategory::factory()->for($otherCompany)->expense()->create();
    $foreignProperty = Property::factory()->for($otherCompany)->create();
    $foreignTransaction = Transaction::factory()->create([
        'company_id' => $otherCompany->id,
        'property_id' => $foreignProperty->id,
        'transaction_category_id' => $foreignCategory->id,
    ]);

    $this->actingAs($owner)->post(route('bill-transactions.store', $bill), [
        'transaction_id' => $foreignTransaction->id,
    ])->assertInvalid(['transaction_id']);

    $alreadyLinkedTransaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'lease_id' => $lease->id,
        'transaction_category_id' => $category->id,
        'bill_id' => $otherBill->id,
    ]);

    $this->actingAs($owner)->post(route('bill-transactions.store', $bill), [
        'transaction_id' => $alreadyLinkedTransaction->id,
    ])->assertInvalid(['transaction_id']);
});

test('an owner can detach a lançamento from a bill', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billTransactionLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $transaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'lease_id' => $lease->id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
    ]);

    $this->actingAs($owner)->delete(route('bill-transactions.destroy', [$bill, $transaction]))
        ->assertRedirect();

    $transaction->refresh();
    expect($transaction->bill_id)->toBeNull();
    expect($bill->events()->where('type', 'lancamento_desvinculado')->exists())->toBeTrue();
});

test('detaching a transaction that does not belong to the given bill returns 404', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billTransactionLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $otherBill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $transaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'lease_id' => $lease->id,
        'transaction_category_id' => $category->id,
        'bill_id' => $otherBill->id,
    ]);

    $this->actingAs($owner)->delete(route('bill-transactions.destroy', [$bill, $transaction]))
        ->assertNotFound();
});
