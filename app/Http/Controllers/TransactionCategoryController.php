<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\TransactionCategoryStoreRequest;
use App\Http\Requests\TransactionCategoryUpdateRequest;
use App\Models\TransactionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionCategoryController extends Controller
{
    /**
     * List the transaction categories of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TransactionCategory::class);

        return Inertia::render('transaction-categories/index', [
            'transactionCategories' => $request->user()->company->transactionCategories()
                ->withCount('transactions')
                ->orderBy('name')
                ->get(),
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Show the form to create a new transaction category.
     */
    public function create(): Response
    {
        $this->authorize('create', TransactionCategory::class);

        return Inertia::render('transaction-categories/create', [
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Store a newly created transaction category.
     */
    public function store(TransactionCategoryStoreRequest $request): RedirectResponse
    {
        TransactionCategory::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria criada com sucesso.')]);

        return to_route('transaction-categories.index');
    }

    /**
     * Show the form to edit an existing transaction category.
     */
    public function edit(TransactionCategory $transactionCategory): Response
    {
        $this->authorize('update', $transactionCategory);

        return Inertia::render('transaction-categories/edit', [
            'transactionCategory' => [
                'id' => $transactionCategory->id,
                'name' => $transactionCategory->name,
                'type' => $transactionCategory->type->value,
            ],
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Update an existing transaction category.
     */
    public function update(TransactionCategoryUpdateRequest $request, TransactionCategory $transactionCategory): RedirectResponse
    {
        $transactionCategory->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria atualizada com sucesso.')]);

        return to_route('transaction-categories.index');
    }

    /**
     * Delete an existing transaction category.
     */
    public function destroy(TransactionCategory $transactionCategory): RedirectResponse
    {
        $this->authorize('delete', $transactionCategory);

        abort_if($transactionCategory->transactions()->exists(), 422, __('Não é possível excluir uma categoria vinculada a lançamentos.'));

        $transactionCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria removida com sucesso.')]);

        return to_route('transaction-categories.index');
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
}
