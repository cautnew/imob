<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

test('a user can be assigned a role and it is checked correctly', function () {
    $user = User::factory()->create();
    Role::create(['name' => 'tester']);

    $user->assignRole('tester');

    expect($user->hasRole('tester'))->toBeTrue();
    expect($user->hasRole('other'))->toBeFalse();
});

test('the role middleware blocks users without the required role', function () {
    Route::middleware(['web', 'auth', 'role:tester'])
        ->get('/_test/role-protected', fn () => 'ok');

    Role::create(['name' => 'tester']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/_test/role-protected');
    $response->assertForbidden();

    $user->assignRole('tester');

    $response = $this->actingAs($user)->get('/_test/role-protected');
    $response->assertOk();
});
