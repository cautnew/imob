<?php

use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use App\Notifications\BillOverdue;
use Illuminate\Support\Facades\Notification;

function notifyOverdueBillsLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

test('it notifies company users once for a bill that just became overdue', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $lease = notifyOverdueBillsLeaseForCompany($company);
    $bill = Bill::factory()->overdue()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);

    $this->artisan('bills:notify-overdue')->assertSuccessful();

    $bill->refresh();
    expect($bill->overdue_notified_at)->not->toBeNull();
    Notification::assertSentTo($user, BillOverdue::class);

    Notification::fake();
    $this->artisan('bills:notify-overdue')->assertSuccessful();
    Notification::assertNotSentTo($user, BillOverdue::class);
});

test('bills that are paid or not yet due are not notified', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $lease = notifyOverdueBillsLeaseForCompany($company);

    $paidBill = Bill::factory()->overdue()->paid()->create(['company_id' => $company->id, 'lease_id' => $lease->id]);
    $futureBill = Bill::factory()->create(['company_id' => $company->id, 'lease_id' => $lease->id, 'due_date' => now()->addDays(5)]);

    $this->artisan('bills:notify-overdue')->assertSuccessful();

    expect($paidBill->fresh()->overdue_notified_at)->toBeNull();
    expect($futureBill->fresh()->overdue_notified_at)->toBeNull();
    Notification::assertNotSentTo($user, BillOverdue::class);
});
