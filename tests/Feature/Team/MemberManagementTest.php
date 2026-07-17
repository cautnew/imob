<?php

use App\Models\Company;
use App\Models\User;

test('an owner can view the team members screen', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $response = $this->actingAs($owner)->get(route('team.index'));

    $response->assertOk();
});

test('an owner can add a new member to their company', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $response = $this->actingAs($owner)->post(route('team.store'), [
        'name' => 'Colleague',
        'email' => 'colleague@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('team.index'));

    $member = User::where('email', 'colleague@example.com')->first();

    expect($member)->not->toBeNull();
    expect($member->company_id)->toBe($owner->company_id);
    expect($member->is_owner)->toBeFalse();
});

test('a non-owner cannot view or add team members', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('team.index'))->assertForbidden();

    $this->actingAs($member)->post(route('team.store'), [
        'name' => 'Another Colleague',
        'email' => 'another@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    expect(User::where('email', 'another@example.com')->exists())->toBeFalse();
});

test('a new member cannot see or access the team management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);

    $this->actingAs($member)->get(route('dashboard'))->assertOk();
});
