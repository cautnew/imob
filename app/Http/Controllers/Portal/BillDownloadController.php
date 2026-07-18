<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\EnsuresLesseeOwnership;
use App\Models\Bill;
use App\Models\Lessee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillDownloadController extends Controller
{
    use EnsuresLesseeOwnership;

    /**
     * Download the bill's PDF, streamed from storage (never a public URL).
     */
    public function download(Request $request, Bill $bill): StreamedResponse
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $this->ensureLesseeOwnsBill($bill, $lessee);

        abort_if($bill->path === null, 404);

        return Storage::disk($bill->disk)->download($bill->path, $bill->original_filename);
    }
}
