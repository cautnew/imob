<?php

namespace App\Http\Controllers;

use App\Enums\MaritalStatus;
use App\Http\Requests\LesseeStoreRequest;
use App\Http\Requests\LesseeUpdateRequest;
use App\Models\Lessee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LesseeController extends Controller
{
    /**
     * List the lessees of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lessee::class);

        $state = $request->string('state')->value() ?: null;
        $search = $request->string('search')->value() ?: null;

        return Inertia::render('lessees/index', [
            'lessees' => $request->user()->company->lessees()
                ->withCount('properties')
                ->when($state, fn ($query, $value) => $query->where('state', $value))
                ->when($search, fn ($query, $value) => $query
                    ->where(fn ($query) => $query
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('document', 'like', "%{$value}%")))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => [
                'state' => $state,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form to create a new lessee.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Lessee::class);

        return Inertia::render('lessees/create', $this->formOptions($request));
    }

    /**
     * Store a newly created lessee.
     */
    public function store(LesseeStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $propertyIds = $validated['property_ids'] ?? [];
        unset($validated['property_ids']);

        DB::transaction(function () use ($validated, $propertyIds): void {
            $lessee = Lessee::create($validated);

            $lessee->properties()->sync($propertyIds);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Inquilino criado com sucesso.')]);

        return to_route('lessees.index');
    }

    /**
     * Show the form to edit an existing lessee.
     */
    public function edit(Request $request, Lessee $lessee): Response
    {
        $this->authorize('update', $lessee);

        return Inertia::render('lessees/edit', [
            'lessee' => [
                ...$lessee->only(
                    'id', 'name', 'birth_date', 'occupation',
                    'document', 'rg', 'rg_issuer',
                    'phone', 'mobile', 'email',
                    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
                    'monthly_income',
                ),
                'birth_date' => $lessee->birth_date?->toDateString(),
                'marital_status' => $lessee->marital_status?->value,
                'property_ids' => $lessee->properties()->pluck('properties.id'),
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing lessee.
     */
    public function update(LesseeUpdateRequest $request, Lessee $lessee): RedirectResponse
    {
        $validated = $request->validated();
        $propertyIds = $validated['property_ids'] ?? [];
        unset($validated['property_ids']);

        DB::transaction(function () use ($lessee, $validated, $propertyIds): void {
            $lessee->update($validated);

            $lessee->properties()->sync($propertyIds);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Inquilino atualizado com sucesso.')]);

        return to_route('lessees.index');
    }

    /**
     * Delete an existing lessee.
     */
    public function destroy(Lessee $lessee): RedirectResponse
    {
        $this->authorize('delete', $lessee);

        $lessee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Inquilino removido com sucesso.')]);

        return to_route('lessees.index');
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
                ->get(['id', 'title', 'city', 'state']),
            'maritalStatuses' => $this->maritalStatusOptions(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function maritalStatusOptions(): array
    {
        return array_map(
            fn (MaritalStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            MaritalStatus::cases(),
        );
    }
}
