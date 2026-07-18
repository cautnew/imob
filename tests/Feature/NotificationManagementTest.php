<?php

use App\Models\Bill;
use App\Models\Lease;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use App\Notifications\BillMarkedAsPaid;

test('a user can mark a single notification as read', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        'property_id' => Property::factory()->for($owner->company)->create()->id,
        'owner_id' => Owner::factory()->for($owner->company)->create()->id,
        'lessee_id' => Lessee::factory()->for($owner->company)->create()->id,
    ]);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $owner->notify(new BillMarkedAsPaid($bill));
    $notification = $owner->notifications()->first();

    $this->actingAs($owner)->patch(route('notifications.read', $notification))->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('a user can mark all notifications as read', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        'property_id' => Property::factory()->for($owner->company)->create()->id,
        'owner_id' => Owner::factory()->for($owner->company)->create()->id,
        'lessee_id' => Lessee::factory()->for($owner->company)->create()->id,
    ]);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $owner->notify(new BillMarkedAsPaid($bill));
    $owner->notify(new BillMarkedAsPaid($bill));

    $this->actingAs($owner)->patch(route('notifications.read-all'))->assertRedirect();

    expect($owner->unreadNotifications()->count())->toBe(0);
});

test('a user cannot mark another user notification as read', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherOwner = User::factory()->create(['is_owner' => true]);
    $lease = Lease::factory()->for($owner->company)->create([
        'property_id' => Property::factory()->for($owner->company)->create()->id,
        'owner_id' => Owner::factory()->for($owner->company)->create()->id,
        'lessee_id' => Lessee::factory()->for($owner->company)->create()->id,
    ]);
    $bill = Bill::factory()->create(['company_id' => $owner->company_id, 'lease_id' => $lease->id]);
    $owner->notify(new BillMarkedAsPaid($bill));
    $notification = $owner->notifications()->first();

    $this->actingAs($otherOwner)->patch(route('notifications.read', $notification))->assertNotFound();
});
