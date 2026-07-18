<?php

namespace App\Http\Controllers;

use App\Enums\LeaseEventType;
use App\Http\Requests\LeaseDocumentStoreRequest;
use App\Models\Lease;
use App\Models\LeaseDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LeaseDocumentController extends Controller
{
    /**
     * Attach a new named document to the lease.
     */
    public function store(LeaseDocumentStoreRequest $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('file');
        $path = $file->store("leases/{$lease->id}", 'public');

        DB::transaction(function () use ($lease, $validated, $file, $path): void {
            $lease->documents()->create([
                'name' => $validated['name'],
                'disk' => 'public',
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $lease->events()->create([
                'type' => LeaseEventType::DocumentAttached,
                'occurred_on' => now(),
                'description' => sprintf('Documento anexado: %s.', $validated['name']),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Documento anexado com sucesso.')]);

        return back();
    }

    /**
     * Remove a document from the lease.
     */
    public function destroy(Lease $lease, LeaseDocument $document): RedirectResponse
    {
        $this->authorize('update', $lease);
        abort_unless($document->lease_id === $lease->id, 404);

        DB::transaction(function () use ($lease, $document): void {
            Storage::disk($document->disk)->delete($document->path);

            $lease->events()->create([
                'type' => LeaseEventType::DocumentRemoved,
                'occurred_on' => now(),
                'description' => sprintf('Documento removido: %s.', $document->name),
            ]);

            $document->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Documento removido com sucesso.')]);

        return back();
    }
}
