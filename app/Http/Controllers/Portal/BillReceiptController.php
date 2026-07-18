<?php

namespace App\Http\Controllers\Portal;

use App\Enums\BillEventType;
use App\Enums\BillReceiptStatus;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\BillReceiptStoreRequest;
use App\Models\Bill;
use App\Models\Lessee;
use App\Notifications\BillReceiptUploaded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class BillReceiptController extends Controller
{
    /**
     * Upload a payment receipt for the bill, moving it (and its pending
     * lançamentos) into "aguardando aprovação" until staff review it.
     */
    public function store(BillReceiptStoreRequest $request, Bill $bill): RedirectResponse
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $file = $request->file('file');

        DB::transaction(function () use ($bill, $lessee, $file): void {
            $bill = Bill::whereKey($bill->id)->lockForUpdate()->firstOrFail();

            abort_if(
                $bill->receipts()->where('status', BillReceiptStatus::Pending)->exists(),
                422,
                'Já existe um comprovante aguardando aprovação para este boleto.'
            );

            $path = $file->store("bills/{$bill->id}/receipts", 'public');

            $bill->receipts()->create([
                'company_id' => $lessee->company_id,
                'lessee_id' => $lessee->id,
                'status' => BillReceiptStatus::Pending,
                'disk' => 'public',
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $bill->update(['status' => BillStatus::AwaitingApproval]);

            $bill->transactions()->where('status', TransactionStatus::Pending)->get()
                ->each(fn ($transaction) => $transaction->update([
                    'status' => TransactionStatus::AwaitingApproval,
                ]));

            $bill->events()->create([
                'type' => BillEventType::ReceiptUploaded,
                'occurred_on' => now(),
                'description' => 'Comprovante de pagamento enviado pelo inquilino, aguardando aprovação.',
            ]);

            Notification::send($bill->company->users, new BillReceiptUploaded($bill));
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Comprovante enviado. Aguarde a aprovação da imobiliária.'),
        ]);

        return back();
    }
}
