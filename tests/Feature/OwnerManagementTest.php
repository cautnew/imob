<?php

use App\Models\Company;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingOwnerAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

/**
 * @return array<string, mixed>
 */
function validOwnerPayload(array $overrides = []): array
{
    static $documentFaker;
    $documentFaker ??= FakerFactory::create('pt_BR');

    return array_merge([
        'name' => 'Maria Oliveira',
        'document' => $documentFaker->unique()->cpf(false),
        'phone' => '(11) 4000-0000',
        'mobile' => '(11) 90000-0000',
        'email' => 'maria@example.com',
        'zip_code' => '01310-000',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'complement' => 'Sala 10',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'bank_name' => 'Banco do Brasil',
        'bank_agency' => '1234',
        'bank_account' => '56789-0',
        'bank_account_type' => 'corrente',
        'pix_key' => 'maria@example.com',
    ], $overrides);
}

test('an owner can view, create, edit and delete a proprietário', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('owners.index'))->assertOk();
    $this->actingAs($owner)->get(route('owners.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload());
    $response->assertRedirect(route('owners.index'));

    $proprietario = Owner::where('company_id', $owner->company_id)->first();
    expect($proprietario)->not->toBeNull();
    expect($proprietario->name)->toBe('Maria Oliveira');
    expect($proprietario->bank_account_type->value)->toBe('corrente');

    $this->actingAs($owner)->get(route('owners.edit', $proprietario))->assertOk();

    $this->actingAs($owner)->put(route('owners.update', $proprietario), validOwnerPayload([
        'name' => 'Maria Souza',
    ]))->assertRedirect(route('owners.index'));

    $proprietario->refresh();
    expect($proprietario->name)->toBe('Maria Souza');

    $this->actingAs($owner)->delete(route('owners.destroy', $proprietario))
        ->assertRedirect(route('owners.index'));
    expect(Owner::find($proprietario->id))->toBeNull();
});

test('required basic fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'name' => '',
        'document' => '',
        'phone' => '',
        'zip_code' => '',
        'street' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
    ]))->assertInvalid(['name', 'document', 'phone', 'zip_code', 'street', 'neighborhood', 'city', 'state']);
});

test('document must be a valid cpf or cnpj', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'document' => '111.111.111-11',
    ]))->assertInvalid(['document']);

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'document' => '123',
    ]))->assertInvalid(['document']);
});

test('a proprietário can be registered with a valid cnpj', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $cnpj = FakerFactory::create('pt_BR')->cnpj(false);

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'name' => 'Imobiliária Alfa',
        'document' => $cnpj,
    ]))->assertRedirect(route('owners.index'));

    expect(Owner::where('document', $cnpj)->exists())->toBeTrue();
});

test('document must be unique within the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $payload = validOwnerPayload();

    $this->actingAs($owner)->post(route('owners.store'), $payload)
        ->assertRedirect(route('owners.index'));

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'document' => $payload['document'],
    ]))->assertInvalid(['document']);
});

test('a proprietário can be related to properties owned by the company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $response = $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'property_ids' => [$property->id],
    ]));
    $response->assertRedirect(route('owners.index'));

    $proprietario = Owner::first();
    expect($proprietario->properties()->pluck('properties.id')->all())->toBe([$property->id]);
});

test('property_ids must belong to the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCompany = Company::factory()->create();
    $foreignProperty = Property::factory()->for($otherCompany)->create();

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'property_ids' => [$foreignProperty->id],
    ]))->assertInvalid(['property_ids.0']);
});

test('updating a proprietário replaces its previous property relations', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $firstProperty = Property::factory()->for($owner->company)->create();
    $secondProperty = Property::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'property_ids' => [$firstProperty->id],
    ]));
    $proprietario = Owner::first();

    $this->actingAs($owner)->put(route('owners.update', $proprietario), validOwnerPayload([
        'property_ids' => [$secondProperty->id],
    ]))->assertRedirect(route('owners.index'));

    expect($proprietario->properties()->pluck('properties.id')->all())->toBe([$secondProperty->id]);
});

test('a user without permission cannot access any owner management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $proprietario = Owner::factory()->for($company)->create();

    $this->actingAs($member)->get(route('owners.index'))->assertForbidden();
    $this->actingAs($member)->get(route('owners.create'))->assertForbidden();
    $this->actingAs($member)->post(route('owners.store'), validOwnerPayload())->assertForbidden();
    $this->actingAs($member)->get(route('owners.edit', $proprietario))->assertForbidden();
    $this->actingAs($member)->put(route('owners.update', $proprietario), validOwnerPayload())->assertForbidden();
    $this->actingAs($member)->delete(route('owners.destroy', $proprietario))->assertForbidden();
});

test('a company administrator cannot edit a proprietário from another company', function () {
    $admin = actingOwnerAdministrator();
    $otherCompany = Company::factory()->create();
    $otherOwner = Owner::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('owners.edit', $otherOwner))->assertForbidden();
});

test('a company administrator cannot update a proprietário from another company', function () {
    $admin = actingOwnerAdministrator();
    $otherCompany = Company::factory()->create();
    $otherOwner = Owner::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->put(route('owners.update', $otherOwner), validOwnerPayload([
        'name' => 'Hacked',
    ]))->assertForbidden();
    expect($otherOwner->fresh()->name)->toBe($otherOwner->name);
});

test('a company administrator cannot delete a proprietário from another company', function () {
    $admin = actingOwnerAdministrator();
    $otherCompany = Company::factory()->create();
    $otherOwner = Owner::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('owners.destroy', $otherOwner))->assertForbidden();
    expect($otherOwner->fresh())->not->toBeNull();
});

test('a company administrator never sees proprietários from another company in the index', function () {
    $admin = actingOwnerAdministrator();
    Owner::factory()->for($admin->company)->create();

    $otherCompany = Company::factory()->create();
    Owner::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('owners.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owners/index')
            ->has('owners.data', 1)
            ->where('owners.data.0.id', $admin->company->owners()->first()->id)
        );
});

test('the index can be filtered by search and state', function () {
    $admin = actingOwnerAdministrator();
    Owner::factory()->for($admin->company)->create(['name' => 'Carlos Andrade', 'state' => 'SP']);
    Owner::factory()->for($admin->company)->create(['name' => 'Beatriz Lima', 'state' => 'RJ']);

    $this->actingAs($admin)->get(route('owners.index', ['search' => 'Carlos']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('owners.data', 1)
            ->where('owners.data.0.name', 'Carlos Andrade')
        );

    $this->actingAs($admin)->get(route('owners.index', ['state' => 'RJ']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('owners.data', 1)
            ->where('owners.data.0.name', 'Beatriz Lima')
        );
});

test('deleting a proprietário removes its property relations', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('owners.store'), validOwnerPayload([
        'property_ids' => [$property->id],
    ]));
    $proprietario = Owner::first();

    $this->actingAs($owner)->delete(route('owners.destroy', $proprietario));

    expect(DB::table('owner_property')->where('owner_id', $proprietario->id)->exists())->toBeFalse();
});
