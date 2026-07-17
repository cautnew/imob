<?php

use App\Models\Company;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Storage::fake('public');
});

function actingPropertyMediaAdministrator(): User
{
    (new PermissionSeeder)->run();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create(['is_owner' => false]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('Administrador');

    return $user;
}

test('an owner can view the media gallery of a property', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    PropertyMedia::factory()->for($property)->cover()->create(['sort_order' => 0]);
    PropertyMedia::factory()->for($property)->create(['sort_order' => 1]);

    $this->actingAs($owner)->get(route('property-media.index', $property))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('properties/media')
            ->has('media', 2)
        );
});

test('an owner can upload multiple photos and the first one becomes the cover', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $files = [
        UploadedFile::fake()->create('sala.jpg', 100, 'image/jpeg'),
        UploadedFile::fake()->create('cozinha.jpg', 100, 'image/jpeg'),
    ];

    $this->actingAs($owner)->post(route('property-media.store', $property), [
        'files' => $files,
    ])->assertRedirect();

    expect($property->media()->count())->toBe(2);

    $ordered = $property->media()->orderBy('sort_order')->get();
    expect($ordered->first()->is_cover)->toBeTrue();
    expect($ordered->last()->is_cover)->toBeFalse();
    expect($ordered->first()->original_filename)->toBe('sala.jpg');

    Storage::disk('public')->assertExists($ordered->first()->path);
});

test('uploaded files must be images', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();

    $this->actingAs($owner)->post(route('property-media.store', $property), [
        'files' => [UploadedFile::fake()->create('document.pdf', 100)],
    ])->assertInvalid(['files.0']);
});

test('an owner can update the caption of a media item', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $media = PropertyMedia::factory()->for($property)->create();

    $this->actingAs($owner)->patch(route('property-media.update', [$property, $media]), [
        'caption' => 'Vista para o jardim',
    ])->assertRedirect();

    expect($media->fresh()->caption)->toBe('Vista para o jardim');
});

test('an owner can set a different media item as the cover photo', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $cover = PropertyMedia::factory()->for($property)->cover()->create();
    $other = PropertyMedia::factory()->for($property)->create();

    $this->actingAs($owner)->post(route('property-media.cover', [$property, $other]))
        ->assertRedirect();

    expect($cover->fresh()->is_cover)->toBeFalse();
    expect($other->fresh()->is_cover)->toBeTrue();
});

test('an owner can reorder the property media', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $first = PropertyMedia::factory()->for($property)->create(['sort_order' => 0]);
    $second = PropertyMedia::factory()->for($property)->create(['sort_order' => 1]);

    $this->actingAs($owner)->post(route('property-media.reorder', $property), [
        'order' => [$second->id, $first->id],
    ])->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(0);
    expect($first->fresh()->sort_order)->toBe(1);
});

test('reordering rejects media ids that belong to another property', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $media = PropertyMedia::factory()->for($property)->create();

    $otherProperty = Property::factory()->for($owner->company)->create();
    $foreignMedia = PropertyMedia::factory()->for($otherProperty)->create();

    $this->actingAs($owner)->post(route('property-media.reorder', $property), [
        'order' => [$media->id, $foreignMedia->id],
    ])->assertInvalid(['order.1']);
});

test('deleting the cover photo promotes another photo as the new cover', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $cover = PropertyMedia::factory()->for($property)->cover()->create(['sort_order' => 0]);
    $other = PropertyMedia::factory()->for($property)->create(['sort_order' => 1]);

    Storage::disk('public')->put($cover->path, 'fake-content');

    $this->actingAs($owner)->delete(route('property-media.destroy', [$property, $cover]))
        ->assertRedirect();

    expect(PropertyMedia::find($cover->id))->toBeNull();
    expect($other->fresh()->is_cover)->toBeTrue();
    Storage::disk('public')->assertMissing($cover->path);
});

test('a user without permission cannot access any property media route', function () {
    $company = Company::factory()->create();
    $member = User::factory()->for($company)->create(['is_owner' => false]);
    $property = Property::factory()->for($company)->create();
    $media = PropertyMedia::factory()->for($property)->create();

    $this->actingAs($member)->get(route('property-media.index', $property))->assertForbidden();
    $this->actingAs($member)->post(route('property-media.store', $property), [
        'files' => [UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')],
    ])->assertForbidden();
    $this->actingAs($member)->patch(route('property-media.update', [$property, $media]), ['caption' => 'x'])->assertForbidden();
    $this->actingAs($member)->post(route('property-media.reorder', $property), ['order' => [$media->id]])->assertForbidden();
    $this->actingAs($member)->post(route('property-media.cover', [$property, $media]))->assertForbidden();
    $this->actingAs($member)->delete(route('property-media.destroy', [$property, $media]))->assertForbidden();
});

test('a company administrator cannot manage media of a property from another company', function () {
    $admin = actingPropertyMediaAdministrator();
    $otherCompany = Company::factory()->create();
    $otherProperty = Property::factory()->for($otherCompany)->create();

    $this->actingAs($admin)->get(route('property-media.index', $otherProperty))->assertForbidden();
});

test('a company administrator cannot delete media of a property from another company', function () {
    $admin = actingPropertyMediaAdministrator();
    $otherCompany = Company::factory()->create();
    $otherProperty = Property::factory()->for($otherCompany)->create();
    $media = PropertyMedia::factory()->for($otherProperty)->create();

    $this->actingAs($admin)->delete(route('property-media.destroy', [$otherProperty, $media]))->assertForbidden();
});

test('a media item that does not belong to the given property returns 404', function () {
    $owner = User::factory()->create(['is_owner' => true]);
    $property = Property::factory()->for($owner->company)->create();
    $otherProperty = Property::factory()->for($owner->company)->create();
    $foreignMedia = PropertyMedia::factory()->for($otherProperty)->create();

    $this->actingAs($owner)->delete(route('property-media.destroy', [$property, $foreignMedia]))
        ->assertNotFound();
});
