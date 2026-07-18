<?php

namespace App\Http\Controllers;

use App\Enums\PropertyPurpose;
use App\Http\Requests\PriceTypeStoreRequest;
use App\Http\Requests\PriceTypeUpdateRequest;
use App\Models\PriceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PriceTypeController extends Controller
{
    /**
     * List the price types of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PriceType::class);

        return Inertia::render('price-types/index', [
            'priceTypes' => $request->user()->company->priceTypes()
                ->withCount('prices')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form to create a new price type.
     */
    public function create(): Response
    {
        $this->authorize('create', PriceType::class);

        return Inertia::render('price-types/create', [
            'purposes' => $this->purposeOptions(),
        ]);
    }

    /**
     * Store a newly created price type.
     */
    public function store(PriceTypeStoreRequest $request): RedirectResponse
    {
        PriceType::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de preço criado com sucesso.')]);

        return to_route('price-types.index');
    }

    /**
     * Show the form to edit an existing price type.
     */
    public function edit(PriceType $priceType): Response
    {
        $this->authorize('update', $priceType);

        return Inertia::render('price-types/edit', [
            'priceType' => [
                ...$priceType->only('id', 'name', 'comparable'),
                'purpose' => $priceType->purpose?->value,
            ],
            'purposes' => $this->purposeOptions(),
        ]);
    }

    /**
     * Update an existing price type.
     */
    public function update(PriceTypeUpdateRequest $request, PriceType $priceType): RedirectResponse
    {
        $priceType->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de preço atualizado com sucesso.')]);

        return to_route('price-types.index');
    }

    /**
     * Delete an existing price type.
     */
    public function destroy(PriceType $priceType): RedirectResponse
    {
        $this->authorize('delete', $priceType);

        abort_if($priceType->prices()->exists(), 422, __('Não é possível excluir um tipo de preço vinculado a imóveis.'));

        $priceType->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de preço removido com sucesso.')]);

        return to_route('price-types.index');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function purposeOptions(): array
    {
        return array_map(
            fn (PropertyPurpose $purpose): array => ['value' => $purpose->value, 'label' => $purpose->label()],
            PropertyPurpose::cases(),
        );
    }
}
