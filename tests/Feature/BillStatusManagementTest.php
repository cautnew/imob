<?php

use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Notifications\BillMarkedAsPaid;
use Illuminate\Support\Facades\Notification;

function billStatusLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

test('marking a bill as paid cascades payment to its linked lançamentos and notifies company users', function () {
    Notification::fake();

    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billStatusLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $pendingTransaction = Transaction::factory()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
        'status' => TransactionStatus::Pending,
    ]);

    $this->actingAs($owner)->patch(route('bills.status.update', $bill), ['status' => 'pago'])
        ->assertRedirect(route('bills.show', $bill));

    $bill->refresh();
    $pendingTransaction->refresh();

    expect($bill->status)->toBe(BillStatus::Paid);
    expect($bill->paid_date)->not->toBeNull();
    expect($pendingTransaction->status)->toBe(TransactionStatus::Paid);
    expect($pendingTransaction->paid_date)->not->toBeNull();
    expect($bill->events()->where('type', 'marcado_como_pago')->exists())->toBeTrue();

    Notification::assertSentTo($owner, BillMarkedAsPaid::class);
});

test('reopening a paid bill reverts its linked lançamentos back to pendente', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billStatusLeaseForCompany($owner->company);
    $bill = Bill::factory()->paid()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($owner->company)->expense()->create();

    $paidTransaction = Transaction::factory()->paid()->create([
        'company_id' => $owner->company_id,
        'property_id' => $lease->property_id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
    ]);

    $this->actingAs($owner)->patch(route('bills.status.update', $bill), ['status' => 'pendente'])
        ->assertRedirect(route('bills.show', $bill));

    $bill->refresh();
    $paidTransaction->refresh();

    expect($bill->status)->toBe(BillStatus::Pending);
    expect($bill->paid_date)->toBeNull();
    expect($paidTransaction->status)->toBe(TransactionStatus::Pending);
    expect($paidTransaction->paid_date)->toBeNull();
    expect($bill->events()->where('type', 'reaberto')->exists())->toBeTrue();
});

test('a user without boletos.editar permission cannot change a bill status', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $lease = billStatusLeaseForCompany($company);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    $this->actingAs($member)->patch(route('bills.status.update', $bill), ['status' => 'pago'])
        ->assertForbidden();
});
