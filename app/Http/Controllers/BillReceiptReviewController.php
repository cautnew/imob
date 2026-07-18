<?php

namespace App\Http\Controllers;

use App\Enums\BillEventType;
use App\Enums\BillReceiptStatus;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Http\Requests\BillReceiptRejectRequest;
use App\Models\Bill;
use App\Models\BillReceipt;
use App\Notifications\BillReceiptApproved;
use App\Notifications\BillReceiptRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BillReceiptReviewController extends Controller
{
    /**
     * Approve a pending receipt, cascading the bill and its linked
     * lançamentos to "pago".
     */
    public function approve(Request $request, Bill $bill, BillReceipt $receipt): RedirectResponse
    {
        $this->authorize('update', $bill);

        abort_unless($receipt->bill_id === $bill->id, 404);
        abort_unless($receipt->status === BillReceiptStatus::Pending, 422);

        DB::transaction(function () use ($bill, $receipt, $request): void {
            $receipt->update([
                'status' => BillReceiptStatus::Approved,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $bill->update(['status' => BillStatus::Paid, 'paid_date' => now()]);

            $bill->transactions()->where('status', TransactionStatus::AwaitingApproval)->get()
                ->each(fn ($transaction) => $transaction->update([
                    'status' => TransactionStatus::Paid,
                    'paid_date' => now(),
                ]));

            $bill->events()->create([
                'type' => BillEventType::ReceiptApproved,
                'occurred_on' => now(),
                'description' => 'Comprovante aprovado. Boleto marcado como pago.',
            ]);

            $receipt->lessee->notify(new BillReceiptApproved($bill));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comprovante aprovado com sucesso.')]);

        return back();
    }

    /**
     * Reject a pending receipt, cascading the bill and its linked
     * lançamentos back to "pendente".
     */
    public function reject(BillReceiptRejectRequest $request, Bill $bill, BillReceipt $receipt): RedirectResponse
    {
        abort_unless($receipt->bill_id === $bill->id, 404);
        abort_unless($receipt->status === BillReceiptStatus::Pending, 422);

        $reason = $request->validated('rejection_reason');

        DB::transaction(function () use ($bill, $receipt, $request, $reason): void {
            $receipt->update([
                'status' => BillReceiptStatus::Rejected,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $bill->update(['status' => BillStatus::Pending]);

            $bill->transactions()->where('status', TransactionStatus::AwaitingApproval)->get()
                ->each(fn ($transaction) => $transaction->update([
                    'status' => TransactionStatus::Pending,
                ]));

            $bill->events()->create([
                'type' => BillEventType::ReceiptRejected,
                'occurred_on' => now(),
                'description' => trim('Comprovante rejeitado. '.($reason ?? '')),
            ]);

            $receipt->lessee->notify(new BillReceiptRejected($bill, $reason));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comprovante rejeitado.')]);

        return back();
    }
}
