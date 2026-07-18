<?php

namespace App\Http\Controllers;

use App\Enums\BillEventType;
use App\Http\Requests\BillPdfUploadRequest;
use App\Models\Bill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillPdfController extends Controller
{
    /**
     * Attach (or replace) the bill's PDF.
     */
    public function store(BillPdfUploadRequest $request, Bill $bill): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store("bills/{$bill->id}", 'public');
        $previousPath = $bill->path;
        $wasReplaced = $previousPath !== null;

        DB::transaction(function () use ($bill, $file, $path, $previousPath, $wasReplaced): void {
            $bill->update([
                'disk' => 'public',
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($previousPath !== null) {
                Storage::disk('public')->delete($previousPath);
            }

            $bill->events()->create([
                'type' => $wasReplaced ? BillEventType::PdfReplaced : BillEventType::PdfUploaded,
                'occurred_on' => now(),
                'description' => $wasReplaced ? 'PDF do boleto atualizado.' : 'PDF do boleto anexado.',
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('PDF anexado com sucesso.')]);

        return back();
    }

    /**
     * Download the bill's PDF.
     */
    public function download(Bill $bill): StreamedResponse
    {
        $this->authorize('view', $bill);
        abort_if($bill->path === null, 404);

        return Storage::disk($bill->disk)->download($bill->path, $bill->original_filename);
    }
}
