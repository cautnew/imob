<?php

namespace App\Http\Controllers\Portal;

use App\Enums\BillReceiptStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\EnsuresLesseeOwnership;
use App\Models\Bill;
use App\Models\Lessee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillController extends Controller
{
    use EnsuresLesseeOwnership;

    /**
     * List bills across every lease belonging to the authenticated lessee.
     */
    public function index(Request $request): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $leaseIds = $lessee->leases()->pluck('id');

        $bills = Bill::whereIn('lease_id', $leaseIds)
            ->with('lease.property:id,title')
            ->withSum('transactions', 'amount')
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        $bills->getCollection()->transform(fn (Bill $bill) => [
            'id' => $bill->id,
            'description' => $bill->description,
            'total_amount' => number_format((float) ($bill->transactions_sum_amount ?? 0), 2, '.', ''),
            'due_date' => $bill->due_date->toDateString(),
            'status' => $bill->effectiveStatus()->value,
            'status_label' => $bill->effectiveStatus()->label(),
            'property' => $bill->lease->property->only('id', 'title'),
        ]);

        return Inertia::render('portal/bills/index', [
            'bills' => $bills,
        ]);
    }

    /**
     * Show a bill's details, cost breakdown, and receipt history.
     */
    public function show(Request $request, Bill $bill): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $this->ensureLesseeOwnsBill($bill, $lessee);

        $bill->load('lease.property:id,title');

        $pendingReceipt = $bill->receipts()
            ->where('status', BillReceiptStatus::Pending)
            ->first();

        return Inertia::render('portal/bills/show', [
            'bill' => [
                'id' => $bill->id,
                'due_date' => $bill->due_date->toDateString(),
                'paid_date' => $bill->paid_date?->toDateString(),
                'description' => $bill->description,
                'status' => $bill->effectiveStatus()->value,
                'status_label' => $bill->effectiveStatus()->label(),
                'total_amount' => $bill->totalAmount(),
                'has_pdf' => $bill->path !== null,
                'property' => $bill->lease->property->only('id', 'title'),
            ],
            'transactions' => $bill->transactions()
                ->with('transactionCategory:id,name')
                ->orderBy('due_date')
                ->get(['id', 'description', 'amount', 'due_date', 'status', 'transaction_category_id'])
                ->map(fn ($transaction) => [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'amount' => (string) $transaction->amount,
                    'due_date' => $transaction->due_date->toDateString(),
                    'status' => $transaction->effectiveStatus()->value,
                    'transaction_category' => $transaction->transactionCategory->only('id', 'name'),
                ]),
            'receipts' => $bill->receipts()
                ->get(['id', 'status', 'original_filename', 'rejection_reason', 'created_at'])
                ->map(fn ($receipt) => [
                    'id' => $receipt->id,
                    'status' => $receipt->status->value,
                    'status_label' => $receipt->status->label(),
                    'original_filename' => $receipt->original_filename,
                    'rejection_reason' => $receipt->rejection_reason,
                    'created_at' => $receipt->created_at->toDateString(),
                ]),
            'canUploadReceipt' => $pendingReceipt === null,
        ]);
    }
}
