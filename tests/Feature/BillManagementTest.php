<?php

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingBillAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

function billLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

/**
 * @return array<string, mixed>
 */
function validBillPayload(array $overrides = []): array
{
    return array_merge([
        'due_date' => '2026-08-10',
        'description' => 'Boleto de agosto',
    ], $overrides);
}

test('an owner can view, create, edit and delete a bill', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billLeaseForCompany($owner->company);

    $this->actingAs($owner)->get(route('bills.index'))->assertOk();
    $this->actingAs($owner)->get(route('bills.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('bills.store'), validBillPayload([
        'lease_id' => $lease->id,
    ]));

    $bill = Bill::where('company_id', $owner->company_id)->first();
    expect($bill)->not->toBeNull();
    expect($bill->status)->toBe(BillStatus::Pending);
    $response->assertRedirect(route('bills.show', $bill));

    $this->actingAs($owner)->get(route('bills.show', $bill))->assertOk();
    $this->actingAs($owner)->get(route('bills.edit', $bill))->assertOk();

    $this->actingAs($owner)->put(route('bills.update', $bill), validBillPayload([
        'lease_id' => $lease->id,
        'description' => 'Boleto atualizado',
    ]))->assertRedirect(route('bills.show', $bill));

    $bill->refresh();
    expect($bill->description)->toBe('Boleto atualizado');

    $this->actingAs($owner)->delete(route('bills.destroy', $bill))
        ->assertRedirect(route('bills.index'));
    expect(Bill::find($bill->id))->toBeNull();
});

test('required fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('bills.store'), validBillPayload([
        'lease_id' => '',
        'due_date' => '',
    ]))->assertInvalid(['lease_id', 'due_date']);
});

test('lease_id must belong to the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCompany = Company::factory()->create();
    $foreignLease = billLeaseForCompany($otherCompany);

    $this->actingAs($owner)->post(route('bills.store'), validBillPayload([
        'lease_id' => $foreignLease->id,
    ]))->assertInvalid(['lease_id']);
});

test('a bill created via store logs a criado event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billLeaseForCompany($owner->company);

    $this->actingAs($owner)->post(route('bills.store'), validBillPayload([
        'lease_id' => $lease->id,
    ]));

    $bill = Bill::first();
    expect($bill->events()->where('type', 'criado')->exists())->toBeTrue();
});

test('a pending bill past its due date is reported as vencido without being written', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = billLeaseForCompany($owner->company);
    $bill = Bill::factory()->overdue()->create([
        'company_id' => $owner->company_id,
        'lease_id' => $lease->id,
    ]);

    expect($bill->effectiveStatus())->toBe(BillStatus::Overdue);
    expect($bill->getRawOriginal('status'))->toBe('pendente');

    $this->actingAs($owner)->get(route('bills.index', ['status' => 'vencido']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('bills/index')
            ->has('bills.data', 1)
            ->where('bills.data.0.status', 'vencido')
        );
});

test('a user without permission cannot access any bill management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $lease = billLeaseForCompany($company);
    $bill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    $this->actingAs($member)->get(route('bills.index'))->assertForbidden();
    $this->actingAs($member)->get(route('bills.create'))->assertForbidden();
    $this->actingAs($member)->post(route('bills.store'), validBillPayload())->assertForbidden();
    $this->actingAs($member)->get(route('bills.show', $bill))->assertForbidden();
    $this->actingAs($member)->get(route('bills.edit', $bill))->assertForbidden();
    $this->actingAs($member)->put(route('bills.update', $bill), validBillPayload())->assertForbidden();
    $this->actingAs($member)->delete(route('bills.destroy', $bill))->assertForbidden();
});

test('a company administrator never sees bills from another company in the index', function () {
    $admin = actingBillAdministrator();
    $lease = billLeaseForCompany($admin->company);
    Bill::factory()->create(['company_id' => $admin->company_id, 'lease_id' => $lease->id]);

    $otherCompany = Company::factory()->create();
    $otherLease = billLeaseForCompany($otherCompany);
    Bill::factory()->create(['company_id' => $otherCompany->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($admin)->get(route('bills.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('bills/index')
            ->has('bills.data', 1)
        );
});

test('a company administrator cannot view a bill from another company', function () {
    $admin = actingBillAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = billLeaseForCompany($otherCompany);
    $otherBill = Bill::factory()->create(['company_id' => $otherCompany->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($admin)->get(route('bills.show', $otherBill))->assertForbidden();
});

test('a company administrator cannot edit a bill from another company', function () {
    $admin = actingBillAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = billLeaseForCompany($otherCompany);
    $otherBill = Bill::factory()->create(['company_id' => $otherCompany->id, 'lease_id' => $otherLease->id]);

    $this->actingAs($admin)->get(route('bills.edit', $otherBill))->assertForbidden();
});
