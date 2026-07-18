<?php

namespace App\Http\Controllers\Portal;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\Lessee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * List the authenticated lessee's payment history (paid transactions).
     */
    public function index(Request $request): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $leaseIds = $lessee->leases()->pluck('id');

        $payments = Transaction::whereIn('lease_id', $leaseIds)
            ->where('status', TransactionStatus::Paid)
            ->with('transactionCategory:id,name')
            ->orderByDesc('paid_date')
            ->paginate(15)
            ->withQueryString();

        $payments->getCollection()->transform(fn (Transaction $transaction) => [
            'id' => $transaction->id,
            'description' => $transaction->description,
            'amount' => (string) $transaction->amount,
            'paid_date' => $transaction->paid_date?->toDateString(),
            'transaction_category' => $transaction->transactionCategory->only('id', 'name'),
        ]);

        return Inertia::render('portal/payments/index', [
            'payments' => $payments,
        ]);
    }
}
