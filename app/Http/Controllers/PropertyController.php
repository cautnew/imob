<?php

namespace App\Http\Controllers;

use App\Enums\PriceFrequency;
use App\Enums\PropertyAttributeType;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Http\Requests\PropertyStoreRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Models\Property;
use App\Models\PropertyAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    /**
     * List the properties of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $status = $request->string('status')->value() ?: null;
        $purpose = $request->string('purpose')->value() ?: null;
        $search = $request->string('search')->value() ?: null;

        return Inertia::render('properties/index', [
            'properties' => $request->user()->company->properties()
                ->withCount('prices')
                ->when($status, fn ($query, $value) => $query->where('status', $value))
                ->when($purpose, fn ($query, $value) => $query->where('purpose', $value))
                ->when($search, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => [
                'status' => $status,
                'purpose' => $purpose,
                'search' => $search,
            ],
            'statuses' => $this->statusOptions(),
            'purposes' => $this->purposeOptions(),
        ]);
    }

    /**
     * Show the form to create a new property.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Property::class);

        return Inertia::render('properties/create', $this->formOptions($request));
    }

    /**
     * Store a newly created property.
     */
    public function store(PropertyStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $features = $validated['features'] ?? [];
        $attributes = $validated['attributes'] ?? [];
        $prices = $validated['prices'] ?? [];
        unset($validated['features'], $validated['attributes'], $validated['prices']);

        DB::transaction(function () use ($validated, $features, $attributes, $prices): void {
            $property = Property::create($validated);

            $property->features()->sync($features);
            $this->syncAttributeValues($property, $attributes);
            $this->syncPrices($property, $prices);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Imóvel criado com sucesso.')]);

        return to_route('properties.index');
    }

    /**
     * Show the form to edit an existing property.
     */
    public function edit(Request $request, Property $property): Response
    {
        $this->authorize('update', $property);

        return Inertia::render('properties/edit', [
            'property' => [
                ...$property->only(
                    'id', 'title', 'slug', 'description', 'zip_code', 'street', 'number', 'complement',
                    'neighborhood', 'city', 'state', 'latitude', 'longitude', 'total_area', 'built_area',
                    'is_public',
                ),
                'purpose' => $property->purpose->value,
                'type' => $property->type->value,
                'status' => $property->status->value,
                'feature_ids' => $property->features()->pluck('features.id'),
                'attribute_values' => $property->attributeValues()
                    ->get(['property_attribute_id', 'property_attribute_option_id', 'value'])
                    ->groupBy('property_attribute_id')
                    ->map(fn ($values, $attributeId) => $values->count() > 1
                        ? $values->pluck('property_attribute_option_id')->all()
                        : ($values->first()->property_attribute_option_id ?? $values->first()->value))
                    ->all(),
                'prices' => $property->prices()
                    ->get(['price_type_id', 'amount', 'frequency'])
                    ->map(fn ($price) => [
                        'price_type_id' => $price->price_type_id,
                        'amount' => (string) $price->amount,
                        'frequency' => $price->frequency->value,
                    ]),
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing property.
     */
    public function update(PropertyUpdateRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();
        $features = $validated['features'] ?? [];
        $attributes = $validated['attributes'] ?? [];
        $prices = $validated['prices'] ?? [];
        unset($validated['features'], $validated['attributes'], $validated['prices']);

        DB::transaction(function () use ($property, $validated, $features, $attributes, $prices): void {
            $property->update($validated);

            $property->features()->sync($features);
            $this->syncAttributeValues($property, $attributes);
            $this->syncPrices($property, $prices);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Imóvel atualizado com sucesso.')]);

        return to_route('properties.index');
    }

    /**
     * Delete an existing property.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $property->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Imóvel removido com sucesso.')]);

        return to_route('properties.index');
    }

    /**
     * Shared reference data used by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        return [
            'purposes' => $this->purposeOptions(),
            'types' => $this->typeOptions(),
            'statuses' => $this->statusOptions(),
            'featureCategories' => $request->user()->company->featureCategories()
                ->where('active', true)
                ->with(['features' => fn ($query) => $query->where('active', true)->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name']),
            'propertyAttributes' => $request->user()->company->propertyAttributes()
                ->with('options:id,property_attribute_id,value')
                ->orderBy('name')
                ->get()
                ->map(fn (PropertyAttribute $attribute) => [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type->value,
                    'required' => $attribute->required,
                    'options' => $attribute->options,
                ]),
            'priceTypes' => $request->user()->company->priceTypes()
                ->orderBy('name')
                ->get(['id', 'name']),
            'frequencies' => $this->frequencyOptions(),
        ];
    }

    /**
     * Replace the property's dynamic attribute values with the given input.
     *
     * @param  array<int|string, mixed>  $attributeInputs
     */
    private function syncAttributeValues(Property $property, array $attributeInputs): void
    {
        $property->attributeValues()->delete();

        if ($attributeInputs === []) {
            return;
        }

        $attributes = $property->company->propertyAttributes()
            ->whereIn('id', array_keys($attributeInputs))
            ->get()
            ->keyBy('id');

        foreach ($attributeInputs as $attributeId => $value) {
            $attribute = $attributes->get((int) $attributeId);

            if (! $attribute || $value === null || $value === '') {
                continue;
            }

            if ($attribute->type === PropertyAttributeType::Multiselect) {
                foreach ((array) $value as $optionId) {
                    $property->attributeValues()->create([
                        'property_attribute_id' => $attribute->id,
                        'property_attribute_option_id' => $optionId,
                    ]);
                }

                continue;
            }

            if ($attribute->type === PropertyAttributeType::Select) {
                $property->attributeValues()->create([
                    'property_attribute_id' => $attribute->id,
                    'property_attribute_option_id' => $value,
                ]);

                continue;
            }

            $property->attributeValues()->create([
                'property_attribute_id' => $attribute->id,
                'value' => is_bool($value) ? (int) $value : (string) $value,
            ]);
        }
    }

    /**
     * Replace the property's dynamic price entries with the given input.
     *
     * @param  array<int, array{price_type_id: int, amount: float|string, frequency: string}>  $prices
     */
    private function syncPrices(Property $property, array $prices): void
    {
        $property->prices()->delete();

        foreach ($prices as $price) {
            $property->prices()->create([
                'price_type_id' => $price['price_type_id'],
                'amount' => $price['amount'],
                'frequency' => $price['frequency'],
            ]);
        }
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

    /**
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(
            fn (PropertyType $type): array => ['value' => $type->value, 'label' => $type->label()],
            PropertyType::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (PropertyStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            PropertyStatus::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function frequencyOptions(): array
    {
        return array_map(
            fn (PriceFrequency $frequency): array => ['value' => $frequency->value, 'label' => $frequency->label()],
            PriceFrequency::cases(),
        );
    }
}
