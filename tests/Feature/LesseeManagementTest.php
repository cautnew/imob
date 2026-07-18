<?php

use App\Models\Company;
use App\Models\Lessee;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

function actingLesseeAdministrator(): User
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
function validLesseePayload(array $overrides = []): array
{
    static $documentFaker;
    $documentFaker ??= FakerFactory::create('pt_BR');

    return array_merge([
        'name' => 'João Pereira',
        'birth_date' => '1990-05-10',
        'marital_status' => 'solteiro',
        'occupation' => 'Engenheiro',
        'document' => $documentFaker->unique()->cpf(false),
        'rg' => '12.345.678-9',
        'rg_issuer' => 'SSP',
        'phone' => '(11) 4000-0000',
        'mobile' => '(11) 90000-0000',
        'email' => 'joao@example.com',
        'zip_code' => '01310-000',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'complement' => 'Apto 10',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'monthly_income' => '5000.00',
    ], $overrides);
}

test('an owner can view, create, edit and delete an inquilino', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->get(route('lessees.index'))->assertOk();
    $this->actingAs($owner)->get(route('lessees.create'))->assertOk();

    $response = $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload());
    $response->assertRedirect(route('lessees.index'));

    $inquilino = Lessee::where('company_id', $owner->company_id)->first();
    expect($inquilino)->not->toBeNull();
    expect($inquilino->name)->toBe('João Pereira');
    expect($inquilino->marital_status->value)->toBe('solteiro');

    $this->actingAs($owner)->get(route('lessees.edit', $inquilino))->assertOk();

    $this->actingAs($owner)->put(route('lessees.update', $inquilino), validLesseePayload([
        'name' => 'João Souza',
    ]))->assertRedirect(route('lessees.index'));

    $inquilino->refresh();
    expect($inquilino->name)->toBe('João Souza');

    $this->actingAs($owner)->delete(route('lessees.destroy', $inquilino))
        ->assertRedirect(route('lessees.index'));
    expect(Lessee::find($inquilino->id))->toBeNull();
});

test('required basic fields are validated', function () {
    $owner = User::factory()->create(['is_owner' => true]);

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
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

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'document' => '111.111.111-11',
    ]))->assertInvalid(['document']);

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'document' => '123',
    ]))->assertInvalid(['document']);
});

test('an inquilino can be registered with a valid cnpj', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $cnpj = FakerFactory::create('pt_BR')->cnpj(false);

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'name' => 'Empresa Beta Ltda',
        'document' => $cnpj,
    ]))->assertRedirect(route('lessees.index'));

    expect(Lessee::where('document', $cnpj)->exists())->toBeTrue();
});

test('document must be unique within the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $payload = validLesseePayload();

    $this->actingAs($owner)->post(route('lessees.store'), $payload)
        ->assertRedirect(route('lessees.index'));

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'document' => $payload['document'],
    ]))->assertInvalid(['document']);
});

test('an inquilino can be related to properties owned by the company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $response = $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'property_ids' => [$property->id],
    ]));
    $response->assertRedirect(route('lessees.index'));

    $inquilino = Lessee::first();
    expect($inquilino->properties()->pluck('properties.id')->all())->toBe([$property->id]);
});

test('property_ids must belong to the same company', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $otherCompany = Company::factory()->create();
    $foreignProperty = Property::factory()->for($otherCompany)->create();

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'property_ids' => [$foreignProperty->id],
    ]))->assertInvalid(['property_ids.0']);
});

test('updating an inquilino replaces its previous property relations', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $firstProperty = Property::factory()->for($owner->company)->create();
    $secondProperty = Property::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'property_ids' => [$firstProperty->id],
    ]));
    $inquilino = Lessee::first();

    $this->actingAs($owner)->put(route('lessees.update', $inquilino), validLesseePayload([
        'property_ids' => [$secondProperty->id],
    ]))->assertRedirect(route('lessees.index'));

    expect($inquilino->properties()->pluck('properties.id')->all())->toBe([$secondProperty->id]);
});

test('a user without permission cannot access any lessee management route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $inquilino = Lessee::factory()->for($company)->create();

    $this->actingAs($member)->get(route('lessees.index'))->assertForbidden();
    $this->actingAs($member)->get(route('lessees.create'))->assertForbidden();
    $this->actingAs($member)->post(route('lessees.store'), validLesseePayload())->assertForbidden();
    $this->actingAs($member)->get(route('lessees.edit', $inquilino))->assertForbidden();
    $this->actingAs($member)->put(route('lessees.update', $inquilino), validLesseePayload())->assertForbidden();
    $this->actingAs($member)->delete(route('lessees.destroy', $inquilino))->assertForbidden();
});

test('a company administrator cannot edit an inquilino from another company', function () {
    $admin = actingLesseeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLessee = Lessee::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('lessees.edit', $otherLessee))->assertForbidden();
});

test('a company administrator cannot update an inquilino from another company', function () {
    $admin = actingLesseeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLessee = Lessee::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->put(route('lessees.update', $otherLessee), validLesseePayload([
        'name' => 'Hacked',
    ]))->assertForbidden();
    expect($otherLessee->fresh()->name)->toBe($otherLessee->name);
});

test('a company administrator cannot delete an inquilino from another company', function () {
    $admin = actingLesseeAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLessee = Lessee::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->delete(route('lessees.destroy', $otherLessee))->assertForbidden();
    expect($otherLessee->fresh())->not->toBeNull();
});

test('a company administrator never sees inquilinos from another company in the index', function () {
    $admin = actingLesseeAdministrator();
    Lessee::factory()->for($admin->company)->create();

    $otherCompany = Company::factory()->create();
    Lessee::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('lessees.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessees/index')
            ->has('lessees.data', 1)
            ->where('lessees.data.0.id', $admin->company->lessees()->first()->id)
        );
});

test('the index can be filtered by search and state', function () {
    $admin = actingLesseeAdministrator();
    Lessee::factory()->for($admin->company)->create(['name' => 'Carlos Andrade', 'state' => 'SP']);
    Lessee::factory()->for($admin->company)->create(['name' => 'Beatriz Lima', 'state' => 'RJ']);

    $this->actingAs($admin)->get(route('lessees.index', ['search' => 'Carlos']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('lessees.data', 1)
            ->where('lessees.data.0.name', 'Carlos Andrade')
        );

    $this->actingAs($admin)->get(route('lessees.index', ['state' => 'RJ']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('lessees.data', 1)
            ->where('lessees.data.0.name', 'Beatriz Lima')
        );
});

test('deleting an inquilino removes its property relations', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('lessees.store'), validLesseePayload([
        'property_ids' => [$property->id],
    ]));
    $inquilino = Lessee::first();

    $this->actingAs($owner)->delete(route('lessees.destroy', $inquilino));

    expect(DB::table('lessee_property')->where('lessee_id', $inquilino->id)->exists())->toBeFalse();
});
