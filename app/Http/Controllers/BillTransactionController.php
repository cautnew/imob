<?php

namespace App\Http\Controllers;

use App\Enums\BillEventType;
use App\Http\Requests\BillTransactionAttachRequest;
use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BillTransactionController extends Controller
{
    /**
     * Attach an existing lançamento (from the same lease) to the bill.
     */
    public function store(BillTransactionAttachRequest $request, Bill $bill): RedirectResponse
    {
        $transaction = Transaction::findOrFail($request->validated('transaction_id'));

        DB::transaction(function () use ($bill, $transaction): void {
            $transaction->update(['bill_id' => $bill->id]);

            $bill->events()->create([
                'type' => BillEventType::TransactionAttached,
                'occurred_on' => now(),
                'description' => sprintf('Lançamento vinculado: %s.', $transaction->description),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento vinculado com sucesso.')]);

        return back();
    }

    /**
     * Detach a lançamento from the bill.
     */
    public function destroy(Bill $bill, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $bill);
        abort_unless($transaction->bill_id === $bill->id, 404);

        DB::transaction(function () use ($bill, $transaction): void {
            $transaction->update(['bill_id' => null]);

            $bill->events()->create([
                'type' => BillEventType::TransactionDetached,
                'occurred_on' => now(),
                'description' => sprintf('Lançamento desvinculado: %s.', $transaction->description),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento desvinculado com sucesso.')]);

        return back();
    }
}
