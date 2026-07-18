<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * List the transactions of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Transaction::class);

        $type = $request->string('type')->value() ?: null;
        $status = $request->string('status')->value() ?: null;
        $propertyId = $request->integer('property_id') ?: null;
        $search = $request->string('search')->value() ?: null;

        $transactions = $request->user()->company->transactions()
            ->with(['property:id,title', 'transactionCategory:id,name,type'])
            ->when($type, fn ($query, $value) => $query
                ->whereHas('transactionCategory', fn ($query) => $query->where('type', $value)))
            ->when($status === TransactionStatus::Paid->value, fn ($query) => $query
                ->where('status', TransactionStatus::Paid))
            ->when($status === TransactionStatus::Pending->value, fn ($query) => $query->open())
            ->when($status === TransactionStatus::Overdue->value, fn ($query) => $query->overdue())
            ->when($propertyId, fn ($query, $value) => $query->where('property_id', $value))
            ->when($search, fn ($query, $value) => $query->where('description', 'like', "%{$value}%"))
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        $transactions->getCollection()->transform(fn (Transaction $transaction) => [
            'id' => $transaction->id,
            'description' => $transaction->description,
            'amount' => (string) $transaction->amount,
            'due_date' => $transaction->due_date->toDateString(),
            'status' => $transaction->effectiveStatus()->value,
            'property' => $transaction->property->only('id', 'title'),
            'transaction_category' => [
                'id' => $transaction->transactionCategory->id,
                'name' => $transaction->transactionCategory->name,
                'type' => $transaction->transactionCategory->type->value,
            ],
        ]);

        return Inertia::render('transactions/index', [
            'transactions' => $transactions,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'property_id' => $propertyId,
                'search' => $search,
            ],
            'types' => $this->typeOptions(),
            'statuses' => $this->statusOptions(),
            'properties' => $request->user()->company->properties()
                ->orderBy('title')
                ->get(['id', 'title']),
        ]);
    }

    /**
     * Show the form to create a new transaction.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Transaction::class);

        return Inertia::render('transactions/create', $this->formOptions($request));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(TransactionStoreRequest $request): RedirectResponse
    {
        Transaction::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento criado com sucesso.')]);

        return to_route('transactions.index');
    }

    /**
     * Show the form to edit an existing transaction.
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        return Inertia::render('transactions/edit', [
            'transaction' => [
                'id' => $transaction->id,
                'property_id' => $transaction->property_id,
                'lease_id' => $transaction->lease_id,
                'transaction_category_id' => $transaction->transaction_category_id,
                'description' => $transaction->description,
                'amount' => (string) $transaction->amount,
                'due_date' => $transaction->due_date->toDateString(),
                'notes' => $transaction->notes,
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing transaction.
     */
    public function update(TransactionUpdateRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento atualizado com sucesso.')]);

        return to_route('transactions.index');
    }

    /**
     * Delete an existing transaction.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento removido com sucesso.')]);

        return to_route('transactions.index');
    }

    /**
     * Toggle a transaction between pendente and pago.
     */
    public function toggleStatus(Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        if ($transaction->status === TransactionStatus::Paid) {
            $transaction->update(['status' => TransactionStatus::Pending, 'paid_date' => null]);

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento reaberto com sucesso.')]);
        } else {
            $transaction->update(['status' => TransactionStatus::Paid, 'paid_date' => now()]);

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Lançamento marcado como pago.')]);
        }

        return to_route('transactions.index');
    }

    /**
     * Shared reference data used by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        return [
            'properties' => $request->user()->company->properties()
                ->orderBy('title')
                ->get(['id', 'title']),
            'leases' => $request->user()->company->leases()
                ->with(['property:id,title'])
                ->get(['id', 'property_id']),
            'transactionCategories' => $request->user()->company->transactionCategories()
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(
            fn (TransactionType $type): array => ['value' => $type->value, 'label' => $type->label()],
            TransactionType::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (TransactionStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            TransactionStatus::cases(),
        );
    }
}
