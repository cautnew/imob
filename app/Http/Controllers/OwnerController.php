<?php

namespace App\Http\Controllers;

use App\Enums\BankAccountType;
use App\Http\Requests\OwnerStoreRequest;
use App\Http\Requests\OwnerUpdateRequest;
use App\Models\Owner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    /**
     * List the owners of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Owner::class);

        $state = $request->string('state')->value() ?: null;
        $search = $request->string('search')->value() ?: null;

        return Inertia::render('owners/index', [
            'owners' => $request->user()->company->owners()
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
     * Show the form to create a new owner.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Owner::class);

        return Inertia::render('owners/create', $this->formOptions($request));
    }

    /**
     * Store a newly created owner.
     */
    public function store(OwnerStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $propertyIds = $validated['property_ids'] ?? [];
        unset($validated['property_ids']);

        DB::transaction(function () use ($validated, $propertyIds): void {
            $owner = Owner::create($validated);

            $owner->properties()->sync($propertyIds);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proprietário criado com sucesso.')]);

        return to_route('owners.index');
    }

    /**
     * Show the form to edit an existing owner.
     */
    public function edit(Request $request, Owner $owner): Response
    {
        $this->authorize('update', $owner);

        return Inertia::render('owners/edit', [
            'owner' => [
                ...$owner->only(
                    'id', 'name', 'document', 'phone', 'mobile', 'email',
                    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
                    'bank_name', 'bank_agency', 'bank_account', 'pix_key',
                ),
                'bank_account_type' => $owner->bank_account_type?->value,
                'property_ids' => $owner->properties()->pluck('properties.id'),
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing owner.
     */
    public function update(OwnerUpdateRequest $request, Owner $owner): RedirectResponse
    {
        $validated = $request->validated();
        $propertyIds = $validated['property_ids'] ?? [];
        unset($validated['property_ids']);

        DB::transaction(function () use ($owner, $validated, $propertyIds): void {
            $owner->update($validated);

            $owner->properties()->sync($propertyIds);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proprietário atualizado com sucesso.')]);

        return to_route('owners.index');
    }

    /**
     * Delete an existing owner.
     */
    public function destroy(Owner $owner): RedirectResponse
    {
        $this->authorize('delete', $owner);

        $owner->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proprietário removido com sucesso.')]);

        return to_route('owners.index');
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
            'bankAccountTypes' => $this->bankAccountTypeOptions(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function bankAccountTypeOptions(): array
    {
        return array_map(
            fn (BankAccountType $type): array => ['value' => $type->value, 'label' => $type->label()],
            BankAccountType::cases(),
        );
    }
}
