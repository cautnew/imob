<?php

namespace App\Http\Controllers;

use App\Enums\PropertyAttributeType;
use App\Http\Requests\PropertyAttributeStoreRequest;
use App\Http\Requests\PropertyAttributeUpdateRequest;
use App\Models\PropertyAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PropertyAttributeController extends Controller
{
    /**
     * List the property attributes of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PropertyAttribute::class);

        return Inertia::render('property-attributes/index', [
            'propertyAttributes' => $request->user()->company->propertyAttributes()
                ->withCount('options')
                ->orderBy('name')
                ->get(),
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Show the form to create a new property attribute.
     */
    public function create(): Response
    {
        $this->authorize('create', PropertyAttribute::class);

        return Inertia::render('property-attributes/create', [
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Store a newly created property attribute.
     */
    public function store(PropertyAttributeStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $options = $validated['options'] ?? [];
        unset($validated['options']);

        DB::transaction(function () use ($validated, $options): void {
            $propertyAttribute = PropertyAttribute::create($validated);

            $this->syncOptions($propertyAttribute, $options);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Atributo criado com sucesso.')]);

        return to_route('property-attributes.index');
    }

    /**
     * Show the form to edit an existing property attribute.
     */
    public function edit(PropertyAttribute $propertyAttribute): Response
    {
        $this->authorize('update', $propertyAttribute);

        return Inertia::render('property-attributes/edit', [
            'propertyAttribute' => [
                ...$propertyAttribute->only('id', 'name', 'filterable', 'comparable', 'required'),
                'type' => $propertyAttribute->type->value,
                'options' => $propertyAttribute->options()->orderBy('order')->get(['id', 'value']),
            ],
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Update an existing property attribute.
     */
    public function update(PropertyAttributeUpdateRequest $request, PropertyAttribute $propertyAttribute): RedirectResponse
    {
        $validated = $request->validated();
        $options = $validated['options'] ?? [];
        unset($validated['options']);

        DB::transaction(function () use ($propertyAttribute, $validated, $options): void {
            $propertyAttribute->update($validated);
            $propertyAttribute->options()->delete();

            $this->syncOptions($propertyAttribute, $options);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Atributo atualizado com sucesso.')]);

        return to_route('property-attributes.index');
    }

    /**
     * Delete an existing property attribute.
     */
    public function destroy(PropertyAttribute $propertyAttribute): RedirectResponse
    {
        $this->authorize('delete', $propertyAttribute);

        $propertyAttribute->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Atributo removido com sucesso.')]);

        return to_route('property-attributes.index');
    }

    /**
     * Replace the property attribute's options with the given values, preserving their order.
     *
     * @param  array<int, array{value: string}>  $options
     */
    private function syncOptions(PropertyAttribute $propertyAttribute, array $options): void
    {
        if ($options === []) {
            return;
        }

        $propertyAttribute->options()->createMany(
            collect($options)->values()->map(fn (array $option, int $index): array => [
                'value' => $option['value'],
                'order' => $index,
            ])->all()
        );
    }

    /**
     * Get the available property attribute types for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return collect(PropertyAttributeType::cases())
            ->map(fn (PropertyAttributeType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->all();
    }
}
