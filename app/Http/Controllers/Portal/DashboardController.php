<?php

namespace App\Http\Controllers\Portal;

use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Lessee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the lessee's portal dashboard.
     */
    public function index(Request $request): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $leases = $lessee->leases()->with('property:id,title')->get();
        $leaseIds = $leases->pluck('id');

        $upcomingBills = Bill::whereIn('lease_id', $leaseIds)
            ->whereIn('status', [BillStatus::Pending, BillStatus::AwaitingApproval])
            ->with('lease.property:id,title')
            ->withSum('transactions', 'amount')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentPayments = Transaction::whereIn('lease_id', $leaseIds)
            ->where('status', TransactionStatus::Paid)
            ->orderByDesc('paid_date')
            ->limit(5)
            ->get(['id', 'description', 'amount', 'paid_date']);

        return Inertia::render('portal/dashboard', [
            'leases' => $leases->map(fn ($lease) => [
                'id' => $lease->id,
                'status' => $lease->status->value,
                'status_label' => $lease->status->label(),
                'property' => $lease->property->only('id', 'title'),
                'rent_amount' => (string) $lease->rent_amount,
            ]),
            'upcomingBills' => $upcomingBills->map(fn (Bill $bill) => [
                'id' => $bill->id,
                'due_date' => $bill->due_date->toDateString(),
                'status' => $bill->status->value,
                'status_label' => $bill->status->label(),
                'total_amount' => number_format((float) ($bill->transactions_sum_amount ?? 0), 2, '.', ''),
                'property' => $bill->lease->property->only('id', 'title'),
            ]),
            'recentPayments' => $recentPayments->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'amount' => (string) $transaction->amount,
                'paid_date' => $transaction->paid_date?->toDateString(),
            ]),
        ]);
    }
}
