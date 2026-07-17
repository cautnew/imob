<?php

use App\Models\User;

test('the current company is bound to the container for an authenticated request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    expect(app()->bound('currentCompany'))->toBeTrue();
    expect(app('currentCompany')->id)->toBe($user->company_id);
});

test('the current company is not bound for a guest request', function () {
    $this->get(route('home'))->assertOk();

    expect(app()->bound('currentCompany'))->toBeFalse();
});
