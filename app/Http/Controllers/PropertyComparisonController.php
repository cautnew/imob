<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PropertyComparisonController extends Controller
{
    /**
     * Maximum number of properties that can be compared at once.
     */
    private const MAX_PROPERTIES = 4;

    /**
     * Show a side-by-side comparison of the given properties from the
     * authenticated user's own company portfolio.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(self::MAX_PROPERTIES)
            ->values();

        $properties = $request->user()->company->properties()
            ->whereIn('id', $ids)
            ->with([
                'media' => fn ($query) => $query->where('is_cover', true),
                'features.featureCategory',
                'attributeValues.propertyAttribute',
                'attributeValues.propertyAttributeOption',
                'prices.priceType',
            ])
            ->get()
            ->sortBy(fn (Property $property) => $ids->search($property->id))
            ->values();

        return Inertia::render('properties/compare', [
            'properties' => $properties->map(fn (Property $property) => $this->transformProperty($property)),
            'priceTypes' => $properties->flatMap(fn (Property $property) => $property->prices->pluck('priceType'))
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->map(fn ($priceType) => ['id' => $priceType->id, 'name' => $priceType->name]),
            'features' => $properties->pluck('features')->flatten()
                ->unique('id')
                ->sortBy([['featureCategory.name', 'asc'], ['name', 'asc']])
                ->values()
                ->map(fn (Feature $feature) => ['id' => $feature->id, 'name' => $feature->name, 'category' => $feature->featureCategory->name]),
            'attributes' => $properties->pluck('attributeValues')->flatten()
                ->pluck('propertyAttribute')
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->map(fn (PropertyAttribute $attribute) => ['id' => $attribute->id, 'name' => $attribute->name]),
            'maxProperties' => self::MAX_PROPERTIES,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformProperty(Property $property): array
    {
        $cover = $property->media->first();
        $principalPrice = $property->principalPrice();

        return [
            'id' => $property->id,
            'title' => $property->title,
            'type' => $property->type->label(),
            'purpose' => $property->purpose->label(),
            'status' => $property->status->label(),
            'neighborhood' => $property->neighborhood,
            'city' => $property->city,
            'state' => $property->state,
            'total_area' => (float) $property->total_area,
            'built_area' => $property->built_area !== null ? (float) $property->built_area : null,
            'cover_url' => $cover ? Storage::disk($cover->disk)->url($cover->path) : null,
            'principal_price' => $principalPrice !== null ? (float) $principalPrice->amount : null,
            'prices' => $property->prices->mapWithKeys(fn ($price) => [
                $price->price_type_id => [
                    'amount' => (float) $price->amount,
                    'frequency' => $price->frequency->label(),
                ],
            ]),
            'features' => $property->features->pluck('id'),
            'attributes' => $property->attributeValues
                ->groupBy('property_attribute_id')
                ->map(fn (Collection $values) => $values
                    ->map(fn (PropertyAttributeValue $value) => $value->propertyAttributeOption?->value ?? $value->value)
                    ->filter()
                    ->implode(', ')),
        ];
    }
}
