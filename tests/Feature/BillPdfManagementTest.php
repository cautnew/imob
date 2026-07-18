<?php

use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Storage::fake('public');
});

function actingBillPdfAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

function billPdfLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

test('an owner can attach a pdf to a bill and it logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billPdfLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);

    $this->actingAs($owner)->post(route('bills.pdf.store', $bill), [
        'file' => UploadedFile::fake()->create('boleto.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $bill->refresh();
    expect($bill->path)->not->toBeNull();
    expect($bill->original_filename)->toBe('boleto.pdf');
    Storage::disk('public')->assertExists($bill->path);
    expect($bill->events()->where('type', 'pdf_anexado')->exists())->toBeTrue();
});

test('replacing a pdf removes the previous file from disk and logs a different event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billPdfLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);

    $this->actingAs($owner)->post(route('bills.pdf.store', $bill), [
        'file' => UploadedFile::fake()->create('primeiro.pdf', 100, 'application/pdf'),
    ]);
    $bill->refresh();
    $firstPath = $bill->path;

    $this->actingAs($owner)->post(route('bills.pdf.store', $bill), [
        'file' => UploadedFile::fake()->create('segundo.pdf', 100, 'application/pdf'),
    ]);
    $bill->refresh();

    expect($bill->path)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($bill->path);
    expect($bill->events()->where('type', 'pdf_atualizado')->exists())->toBeTrue();
});

test('a non-pdf file is rejected', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billPdfLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);

    $this->actingAs($owner)->post(route('bills.pdf.store', $bill), [
        'file' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
    ])->assertInvalid(['file']);
});

test('an owner can download the bill pdf', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billPdfLeaseForCompany($owner->company);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);

    $this->actingAs($owner)->post(route('bills.pdf.store', $bill), [
        'file' => UploadedFile::fake()->create('boleto.pdf', 100, 'application/pdf'),
    ]);

    $this->actingAs($owner)->get(route('bills.download', $bill))->assertOk();
});

test('a company administrator cannot attach a pdf to a bill from another company', function () {
    $admin = actingBillPdfAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = billPdfLeaseForCompany($otherCompany);
    $otherBill = Bill::factory()->create(['company_id' => $otherCompany->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($admin)->post(route('bills.pdf.store', $otherBill), [
        'file' => UploadedFile::fake()->create('boleto.pdf', 100, 'application/pdf'),
    ])->assertForbidden();
});
