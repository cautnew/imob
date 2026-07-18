<?php

use App\Models\Company;
use App\Models\Lessee;

function registeredLessee(array $overrides = []): Lessee
{
    return Lessee::factory()->for(Company::factory()->create())->withPassword('correct-password')->create($overrides);
}

test('a lessee can log in with document and password', function () {
    $lessee = registeredLessee();

    $this->post(route('portal.login'), [
        'document' => $lessee->document,
        'password' => 'correct-password',
    ])->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($lessee, 'lessee');
});

test('a lessee cannot log in with the wrong password', function () {
    $lessee = registeredLessee();

    $this->post(route('portal.login'), [
        'document' => $lessee->document,
        'password' => 'wrong-password',
    ])->assertInvalid(['document']);

    $this->assertGuest('lessee');
});

test('a lessee without a password yet cannot log in', function () {
    $lessee = Lessee::factory()->for(Company::factory()->create())->create(['password' => null]);

    $this->post(route('portal.login'), [
        'document' => $lessee->document,
        'password' => 'anything',
    ])->assertInvalid(['document']);

    $this->assertGuest('lessee');
});

test('a lessee can log out of the portal', function () {
    $lessee = registeredLessee();

    $this->actingAs($lessee, 'lessee')
        ->post(route('portal.logout'))
        ->assertRedirect(route('portal.login'));

    $this->assertGuest('lessee');
});

test('repeated failed login attempts are rate limited', function () {
    $lessee = registeredLessee();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('portal.login'), [
            'document' => $lessee->document,
            'password' => 'wrong-password',
        ]);
    }

    $this->post(route('portal.login'), [
        'document' => $lessee->document,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
