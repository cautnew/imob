<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyMediaReorderRequest;
use App\Http\Requests\PropertyMediaStoreRequest;
use App\Http\Requests\PropertyMediaUpdateRequest;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PropertyMediaController extends Controller
{
    /**
     * List the media gallery of the given property.
     */
    public function index(Property $property): Response
    {
        $this->authorize('update', $property);

        return Inertia::render('properties/media', [
            'property' => $property->only('id', 'title'),
            'media' => $this->transformMedia($property),
        ]);
    }

    /**
     * Upload one or more media files to the property gallery.
     */
    public function store(PropertyMediaStoreRequest $request, Property $property): RedirectResponse
    {
        $nextOrder = (int) $property->media()->max('sort_order') + 1;
        $hasCover = $property->media()->where('is_cover', true)->exists();

        foreach ($request->file('files', []) as $file) {
            $path = $file->store("properties/{$property->id}", 'public');

            $property->media()->create([
                'disk' => 'public',
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => $nextOrder++,
                'is_cover' => ! $hasCover,
            ]);

            $hasCover = true;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Fotos enviadas com sucesso.')]);

        return back();
    }

    /**
     * Update the caption of a media item.
     */
    public function update(PropertyMediaUpdateRequest $request, Property $property, PropertyMedia $media): RedirectResponse
    {
        abort_unless($media->property_id === $property->id, 404);

        $media->update($request->validated());

        return back();
    }

    /**
     * Persist the new display order of the property's media items.
     */
    public function reorder(PropertyMediaReorderRequest $request, Property $property): RedirectResponse
    {
        $order = $request->validated('order');

        DB::transaction(function () use ($property, $order): void {
            foreach ($order as $index => $mediaId) {
                $property->media()->whereKey($mediaId)->update(['sort_order' => $index]);
            }
        });

        return back();
    }

    /**
     * Set the given media item as the property's cover photo.
     */
    public function cover(Property $property, PropertyMedia $media): RedirectResponse
    {
        $this->authorize('update', $property);
        abort_unless($media->property_id === $property->id, 404);

        DB::transaction(function () use ($property, $media): void {
            $property->media()->update(['is_cover' => false]);
            $media->update(['is_cover' => true]);
        });

        return back();
    }

    /**
     * Delete a media item from the property gallery.
     */
    public function destroy(Property $property, PropertyMedia $media): RedirectResponse
    {
        $this->authorize('update', $property);
        abort_unless($media->property_id === $property->id, 404);

        Storage::disk($media->disk)->delete($media->path);
        $wasCover = $media->is_cover;
        $media->delete();

        if ($wasCover) {
            $property->media()->first()?->update(['is_cover' => true]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Foto removida com sucesso.')]);

        return back();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function transformMedia(Property $property): Collection
    {
        return $property->media()->get()->map(fn (PropertyMedia $media) => [
            'id' => $media->id,
            'url' => Storage::disk($media->disk)->url($media->path),
            'caption' => $media->caption,
            'is_cover' => $media->is_cover,
            'sort_order' => $media->sort_order,
        ]);
    }
}
