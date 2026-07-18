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
use App\Notifications\BillReceiptUploaded;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

function billReceiptLeaseForLessee(Company $company, Lessee $lessee): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => $lessee->id,
    ]);
}

test('uploading a receipt moves the bill and its pending lançamentos to aguardando_aprovacao and notifies staff', function () {
    Storage::fake('public');
    Notification::fake();

    $company = Company::factory()->create();
    $staff = User::factory()->for($company)->create(['is_owner' => true]);
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = billReceiptLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);
    $category = TransactionCategory::factory()->for($company)->expense()->create();
    $transaction = Transaction::factory()->create([
        'company_id' => $company->id,
        'property_id' => $lease->property_id,
        'transaction_category_id' => $category->id,
        'bill_id' => $bill->id,
        'status' => TransactionStatus::Pending,
    ]);

    $this->actingAs($lessee, 'lessee')
        ->post(route('portal.bill-receipts.store', $bill), [
            'file' => UploadedFile::fake()->create('comprovante.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

    $bill->refresh();
    $transaction->refresh();

    expect($bill->status)->toBe(BillStatus::AwaitingApproval);
    expect($transaction->status)->toBe(TransactionStatus::AwaitingApproval);
    expect($bill->receipts()->where('status', BillReceiptStatus::Pending)->exists())->toBeTrue();
    expect($bill->events()->where('type', 'comprovante_anexado')->exists())->toBeTrue();

    Notification::assertSentTo($staff, BillReceiptUploaded::class);
});

test('a second receipt cannot be uploaded while one is already pending', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = billReceiptLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    BillReceipt::factory()->create([
        'company_id' => $company->id,
        'bill_id' => $bill->id,
        'lessee_id' => $lessee->id,
        'status' => BillReceiptStatus::Pending,
    ]);

    $this->actingAs($lessee, 'lessee')
        ->post(route('portal.bill-receipts.store', $bill), [
            'file' => UploadedFile::fake()->create('comprovante.pdf', 100, 'application/pdf'),
        ])->assertStatus(422);

    expect($bill->receipts()->count())->toBe(1);
});

test('a lessee cannot upload a receipt for a bill that is not theirs', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();

    $otherLessee = Lessee::factory()->for($company)->create();
    $otherLease = billReceiptLeaseForLessee($company, $otherLessee);
    $otherBill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($lessee, 'lessee')
        ->post(route('portal.bill-receipts.store', $otherBill), [
            'file' => UploadedFile::fake()->create('comprovante.pdf', 100, 'application/pdf'),
        ])->assertNotFound();
});
