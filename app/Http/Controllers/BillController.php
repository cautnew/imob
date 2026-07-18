<?php

namespace App\Http\Controllers;

use App\Enums\BillEventType;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Http\Requests\BillStatusUpdateRequest;
use App\Http\Requests\BillStoreRequest;
use App\Http\Requests\BillUpdateRequest;
use App\Models\Bill;
use App\Notifications\BillMarkedAsPaid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BillController extends Controller
{
    /**
     * List the bills of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bill::class);

        $status = $request->string('status')->value() ?: null;
        $leaseId = $request->integer('lease_id') ?: null;
        $search = $request->string('search')->value() ?: null;

        $bills = $request->user()->company->bills()
            ->with(['lease.property:id,title'])
            ->withSum('transactions', 'amount')
            ->when($status === BillStatus::Paid->value, fn ($query) => $query
                ->where('status', BillStatus::Paid))
            ->when($status === BillStatus::Pending->value, fn ($query) => $query->open())
            ->when($status === BillStatus::Overdue->value, fn ($query) => $query->overdue())
            ->when($status === BillStatus::AwaitingApproval->value, fn ($query) => $query
                ->where('status', BillStatus::AwaitingApproval))
            ->when($leaseId, fn ($query, $value) => $query->where('lease_id', $value))
            ->when($search, fn ($query, $value) => $query
                ->whereHas('lease.property', fn ($query) => $query->where('title', 'like', "%{$value}%")))
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        $bills->getCollection()->transform(fn (Bill $bill) => [
            'id' => $bill->id,
            'description' => $bill->description,
            'total_amount' => number_format((float) ($bill->transactions_sum_amount ?? 0), 2, '.', ''),
            'due_date' => $bill->due_date->toDateString(),
            'status' => $bill->effectiveStatus()->value,
            'lease' => [
                'id' => $bill->lease->id,
                'property' => $bill->lease->property->only('id', 'title'),
            ],
        ]);

        return Inertia::render('bills/index', [
            'bills' => $bills,
            'filters' => [
                'status' => $status,
                'lease_id' => $leaseId,
                'search' => $search,
            ],
            'statuses' => $this->statusOptions(),
            'leases' => $request->user()->company->leases()
                ->with(['property:id,title'])
                ->get(['id', 'property_id']),
        ]);
    }

    /**
     * Show the form to create a new bill.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Bill::class);

        return Inertia::render('bills/create', $this->formOptions($request));
    }

    /**
     * Store a newly created bill.
     */
    public function store(BillStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $bill = DB::transaction(function () use ($validated): Bill {
            $bill = Bill::create($validated);

            $bill->events()->create([
                'type' => BillEventType::Created,
                'occurred_on' => now(),
                'description' => 'Boleto criado.',
            ]);

            return $bill;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Boleto criado com sucesso.')]);

        return to_route('bills.show', $bill);
    }

    /**
     * Show a bill's details, linked lançamentos, PDF and timeline.
     */
    public function show(Bill $bill): Response
    {
        $this->authorize('view', $bill);

        $bill->load(['lease.property:id,title', 'lease.lessee:id,name']);

        return Inertia::render('bills/show', [
            'bill' => [
                'id' => $bill->id,
                'due_date' => $bill->due_date->toDateString(),
                'paid_date' => $bill->paid_date?->toDateString(),
                'description' => $bill->description,
                'status' => $bill->effectiveStatus()->value,
                'total_amount' => $bill->totalAmount(),
                'has_pdf' => $bill->path !== null,
                'original_filename' => $bill->original_filename,
                'lease' => [
                    'id' => $bill->lease->id,
                    'property' => $bill->lease->property->only('id', 'title'),
                    'lessee' => $bill->lease->lessee->only('id', 'name'),
                ],
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
            'availableTransactions' => $bill->lease->transactions()
                ->whereNull('bill_id')
                ->orderBy('due_date')
                ->get(['id', 'description', 'amount', 'due_date']),
            'events' => $bill->events()
                ->get(['id', 'type', 'occurred_on', 'description'])
                ->map(fn ($event) => [
                    'id' => $event->id,
                    'type' => $event->type->value,
                    'type_label' => $event->type->label(),
                    'occurred_on' => $event->occurred_on->toDateString(),
                    'description' => $event->description,
                ]),
            'statuses' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the form to edit an existing bill.
     */
    public function edit(Request $request, Bill $bill): Response
    {
        $this->authorize('update', $bill);

        return Inertia::render('bills/edit', [
            'bill' => [
                'id' => $bill->id,
                'lease_id' => $bill->lease_id,
                'due_date' => $bill->due_date->toDateString(),
                'description' => $bill->description,
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing bill.
     */
    public function update(BillUpdateRequest $request, Bill $bill): RedirectResponse
    {
        $bill->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Boleto atualizado com sucesso.')]);

        return to_route('bills.show', $bill);
    }

    /**
     * Delete an existing bill. Linked lançamentos are detached, not deleted.
     */
    public function destroy(Bill $bill): RedirectResponse
    {
        $this->authorize('delete', $bill);

        if ($bill->path !== null) {
            Storage::disk($bill->disk)->delete($bill->path);
        }

        $bill->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Boleto removido com sucesso.')]);

        return to_route('bills.index');
    }

    /**
     * Change the bill's situação, cascading the payment status to its linked lançamentos.
     */
    public function updateStatus(BillStatusUpdateRequest $request, Bill $bill): RedirectResponse
    {
        $newStatus = BillStatus::from($request->validated('status'));

        DB::transaction(function () use ($bill, $newStatus): void {
            if ($newStatus === BillStatus::Paid) {
                $bill->update(['status' => BillStatus::Paid, 'paid_date' => now()]);

                $bill->transactions()->where('status', TransactionStatus::Pending)->get()
                    ->each(fn ($transaction) => $transaction->update([
                        'status' => TransactionStatus::Paid,
                        'paid_date' => now(),
                    ]));

                $bill->events()->create([
                    'type' => BillEventType::MarkedAsPaid,
                    'occurred_on' => now(),
                    'description' => 'Boleto marcado como pago.',
                ]);

                Notification::send($bill->company->users, new BillMarkedAsPaid($bill));
            } else {
                $bill->update(['status' => BillStatus::Pending, 'paid_date' => null]);

                $bill->transactions()->where('status', TransactionStatus::Paid)->get()
                    ->each(fn ($transaction) => $transaction->update([
                        'status' => TransactionStatus::Pending,
                        'paid_date' => null,
                    ]));

                $bill->events()->create([
                    'type' => BillEventType::Reopened,
                    'occurred_on' => now(),
                    'description' => 'Boleto reaberto.',
                ]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Situação do boleto atualizada com sucesso.')]);

        return to_route('bills.show', $bill);
    }

    /**
     * Shared reference data used by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        return [
            'leases' => $request->user()->company->leases()
                ->with(['property:id,title'])
                ->get(['id', 'property_id']),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (BillStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            BillStatus::cases(),
        );
    }
}
