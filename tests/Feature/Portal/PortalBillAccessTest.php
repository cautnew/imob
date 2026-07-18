<?php

use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function portalBillLeaseForLessee(Company $company, Lessee $lessee): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => $lessee->id,
    ]);
}

test('a lessee can view their own bill', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = portalBillLeaseForLessee($company, $lessee);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.bills.show', $bill))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/bills/show')
            ->where('bill.id', $bill->id));
});

test('a lessee cannot view another lessee\'s bill', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();

    $otherLessee = Lessee::factory()->for($company)->create();
    $otherLease = portalBillLeaseForLessee($company, $otherLessee);
    $otherBill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.bills.show', $otherBill))
        ->assertNotFound();
});

test('a lessee cannot download another lessee\'s bill pdf', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();

    $otherLessee = Lessee::factory()->for($company)->create();
    $otherLease = portalBillLeaseForLessee($company, $otherLessee);
    $otherBill = Bill::factory()->create([
        'company_id' => $company->id,
        'lease_id' => $otherLease->id,
        'disk' => 'public',
        'path' => 'bills/1/boleto.pdf',
        'original_filename' => 'boleto.pdf',
    ]);
    Storage::disk('public')->put($otherBill->path, 'conteudo');

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.bills.download', $otherBill))
        ->assertNotFound();
});

test('a lessee can download their own bill pdf', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = portalBillLeaseForLessee($company, $lessee);
    $path = UploadedFile::fake()->create('boleto.pdf', 100, 'application/pdf')->store('bills/1', 'public');
    $bill = Bill::factory()->create([
        'company_id' => $company->id,
        'lease_id' => $lease->id,
        'disk' => 'public',
        'path' => $path,
        'original_filename' => 'boleto.pdf',
    ]);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.bills.download', $bill))
        ->assertOk();
});

test('a lessee\'s bill index only lists bills from their own leases', function () {
    $company = Company::factory()->create();
    $lessee = Lessee::factory()->for($company)->withPassword()->create();
    $lease = portalBillLeaseForLessee($company, $lessee);
    Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    $otherLessee = Lessee::factory()->for($company)->create();
    $otherLease = portalBillLeaseForLessee($company, $otherLessee);
    Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($lessee, 'lessee')
        ->get(route('portal.bills.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/bills/index')
            ->has('bills.data', 1));
});
