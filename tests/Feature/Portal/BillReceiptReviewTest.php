<?php

use App\Enums\BillReceiptStatus;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Models\Bill;
use App\Models\BillReceipt;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Notifications\BillReceiptApproved;
use App\Notifications\BillReceiptRejected;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

function reviewLeaseForLessee(Company $company, Lessee $lessee): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => $lessee->id,
    ]);
}

function actingReceiptAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('approving a receipt cascades the bill and its transactions to pago and notifies the lessee', function () {
    Notification::fake();

    $admin = actingReceiptAdministrator();
    $company = $admin->company;
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = reviewLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create([
        'company_id' => $company->id,
        'lease_id' => $lease->id,
        'status' => BillStatus::AwaitingApproval,
    ]);
    $category = TransactionCategory::factory()->for($company)->expense()->create();
    $transaction = Transaction::factory()->create([
        'company_id' => $company->id,
        'property_id' => $lease->property_id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
        'status' => TransactionStatus::AwaitingApproval,
    ]);
    $receipt = BillReceipt::factory()->create([
        'company_id' => $company->id,
        'bill_id' => $bill->id,
        'lessee_id' => $lessee->id,
        'status' => BillReceiptStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('bill-receipts.approve', [$bill, $receipt]))
        ->assertRedirect();

    $bill->refresh();
    $transaction->refresh();
    $receipt->refresh();

    expect($bill->status)->toBe(BillStatus::Paid);
    expect($bill->paid_date)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::Paid);
    expect($receipt->status)->toBe(BillReceiptStatus::Approved);
    expect($receipt->reviewed_by)->toBe($admin->id);
    expect($bill->events()->where('type', 'comprovante_aprovado')->exists())->toBeTrue();

    Notification::assertSentTo($lessee, BillReceiptApproved::class);
});

test('rejecting a receipt cascades the bill and its transactions back to pendente and notifies the lessee', function () {
    Notification::fake();

    $admin = actingReceiptAdministrator();
    $company = $admin->company;
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = reviewLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create([
        'company_id' => $company->id,
        'lease_id' => $lease->id,
        'status' => BillStatus::AwaitingApproval,
    ]);
    $category = TransactionCategory::factory()->for($company)->expense()->create();
    $transaction = Transaction::factory()->create([
        'company_id' => $company->id,
        'property_id' => $lease->property_id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
        'status' => TransactionStatus::AwaitingApproval,
    ]);
    $receipt = BillReceipt::factory()->create([
        'company_id' => $company->id,
        'bill_id' => $bill->id,
        'lessee_id' => $lessee->id,
        'status' => BillReceiptStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('bill-receipts.reject', [$bill, $receipt]), ['rejection_reason' => 'Valor divergente.'])
        ->assertRedirect();

    $bill->refresh();
    $transaction->refresh();
    $receipt->refresh();

    expect($bill->status)->toBe(BillStatus::Pending);
    expect($transaction->status)->toBe(TransactionStatus::Pending);
    expect($receipt->status)->toBe(BillReceiptStatus::Rejected);
    expect($receipt->rejection_reason)->toBe('Valor divergente.');
    expect($bill->events()->where('type', 'comprovante_rejeitado')->exists())->toBeTrue();

    Notification::assertSentTo($lessee, BillReceiptRejected::class);
});

test('a lessee can upload a new receipt after a previous one was rejected', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = reviewLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    BillReceipt::factory()->rejected()->create([
        'company_id' => $company->id,
        'bill_id' => $bill->id,
        'lessee_id' => $lessee->id,
    ]);

    $this->actingAs($lessee, 'lessee')
        ->post(route('portal.bill-receipts.store', $bill), [
            'file' => UploadedFile::fake()->create('comprovante.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

    expect($bill->receipts()->where('status', BillReceiptStatus::Pending)->count())->toBe(1);
});

test('a staff user without boletos.editar permission cannot approve or reject a receipt', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = reviewLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create([
        'company_id' => $company->id,
        'lease_id' => $lease->id,
        'status' => BillStatus::AwaitingApproval,
    ]);
    $receipt = BillReceipt::factory()->create([
        'company_id' => $company->id,
        'bill_id' => $bill->id,
        'lessee_id' => $lessee->id,
        'status' => BillReceiptStatus::Pending,
    ]);

    $this->actingAs($member)
        ->post(route('bill-receipts.approve', [$bill, $receipt]))
        ->assertForbidden();
});
