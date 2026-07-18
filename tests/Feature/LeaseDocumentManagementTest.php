<?php

use App\Models\Company;
use App\Models\Lease;
use App\Models\LeaseDocument;
use App\Models\Lessee;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Storage::fake('public');
});

function actingLeaseDocumentAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

function createLeaseForCompany(Company $company): Lease
{
    return Lease::factory()->for($company)->create([
        'property_id' => Property::factory()->for($company)->create()->id,
        'owner_id' => Owner::factory()->for($company)->create()->id,
        'lessee_id' => Lessee::factory()->for($company)->create()->id,
    ]);
}

test('an owner can attach a named document to a lease and it logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);

    $response = $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Contrato assinado',
        'file' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ]);
    $response->assertRedirect();

    $document = LeaseDocument::first();
    expect($document)->not->toBeNull();
    expect($document->name)->toBe('Contrato assinado');
    expect($document->lease_id)->toBe($lease->id);
    expect($document->original_filename)->toBe('contrato.pdf');

    Storage::disk('public')->assertExists($document->path);

    expect($lease->events()->where('type', 'documento_anexado')->exists())->toBeTrue();
});

test('a document can be attached at any time, including after the lease already has documents', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Primeiro documento',
        'file' => UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
    ]);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Segundo documento',
        'file' => UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'),
    ]);

    expect($lease->documents()->count())->toBe(2);
    expect($lease->documents()->pluck('name')->all())->toContain('Primeiro documento', 'Segundo documento');
});

test('document name is required', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => '',
        'file' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ])->assertInvalid(['name']);
});

test('document file is required and must be an accepted type', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Documento sem arquivo',
    ])->assertInvalid(['file']);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Executável',
        'file' => UploadedFile::fake()->create('malware.exe', 100),
    ])->assertInvalid(['file']);
});

test('deleting a document removes the file from disk and logs a timeline event', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);

    $this->actingAs($owner)->post(route('lease-documents.store', $lease), [
        'name' => 'Contrato assinado',
        'file' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ]);
    $document = LeaseDocument::first();

    $this->actingAs($owner)->delete(route('lease-documents.destroy', [$lease, $document]))
        ->assertRedirect();

    expect(LeaseDocument::find($document->id))->toBeNull();
    Storage::disk('public')->assertMissing($document->path);
    expect($lease->events()->where('type', 'documento_removido')->exists())->toBeTrue();
});

test('a user without permission cannot attach or remove lease documents', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $lease = createLeaseForCompany($company);
    $document = LeaseDocument::factory()->for($lease)->create();

    $this->actingAs($member)->post(route('lease-documents.store', $lease), [
        'name' => 'Documento',
        'file' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ])->assertForbidden();

    $this->actingAs($member)->delete(route('lease-documents.destroy', [$lease, $document]))->assertForbidden();
});

test('a company administrator cannot attach documents to a lease from another company', function () {
    $admin = actingLeaseDocumentAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = createLeaseForCompany($otherCompany);

    $this->actingAs($admin)->post(route('lease-documents.store', $otherLease), [
        'name' => 'Documento',
        'file' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ])->assertForbidden();
});

test('a company administrator cannot delete documents of a lease from another company', function () {
    $admin = actingLeaseDocumentAdministrator();
    $otherCompany = Company::factory()->create();
    $otherLease = createLeaseForCompany($otherCompany);
    $document = LeaseDocument::factory()->for($otherLease)->create();

    $this->actingAs($admin)->delete(route('lease-documents.destroy', [$otherLease, $document]))->assertForbidden();
});

test('a document that does not belong to the given lease returns 404', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $lease = createLeaseForCompany($owner->company);
    $otherLease = createLeaseForCompany($owner->company);
    $foreignDocument = LeaseDocument::factory()->for($otherLease)->create();

    $this->actingAs($owner)->delete(route('lease-documents.destroy', [$lease, $foreignDocument]))
        ->assertNotFound();
});
