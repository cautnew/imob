<?php

use App\Models\Company;
use App\Models\Lessee;

function unregisteredLessee(array $overrides = []): Lessee
{
    return Lessee::factory()->for(Company::factory()->create())->create(array_merge([
        'document' => '390.533.447-05',
        'email' => 'inquilino@example.com',
        'password' => null,
    ], $overrides));
}

test('a lessee can register by matching document and email, and is logged in', function () {
    $lessee = unregisteredLessee();

    $response = $this->post(route('portal.register'), [
        'document' => $lessee->document,
        'email' => $lessee->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('portal.dashboard'));

    $lessee->refresh();
    expect($lessee->password)->not->toBeNull();
    $this->assertAuthenticatedAs($lessee, 'lessee');
});

test('registration fails generically when no lessee matches the document and email', function () {
    $response = $this->post(route('portal.register'), [
        'document' => '390.533.447-05',
        'email' => 'ninguem@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid(['document', 'email']);
    $this->assertGuest('lessee');
});

test('registration fails with the same generic error when the lessee already has a password', function () {
    $lessee = unregisteredLessee(['password' => 'already-set']);

    $response = $this->post(route('portal.register'), [
        'document' => $lessee->document,
        'email' => $lessee->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid(['document', 'email']);
    $this->assertGuest('lessee');
});

test('the no-match and already-registered failures return identical error messages', function () {
    $genericMessage = 'Não foi possível concluir o cadastro com os dados informados. '
        .'Verifique o CPF e o e-mail cadastrados junto à imobiliária.';

    $this->post(route('portal.register'), [
        'document' => '390.533.447-05',
        'email' => 'ninguem@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInvalid(['document' => $genericMessage, 'email' => $genericMessage]);

    $alreadyRegistered = unregisteredLessee(['document' => '111.444.777-35', 'password' => 'already-set']);

    $this->post(route('portal.register'), [
        'document' => $alreadyRegistered->document,
        'email' => $alreadyRegistered->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInvalid(['document' => $genericMessage, 'email' => $genericMessage]);
});
