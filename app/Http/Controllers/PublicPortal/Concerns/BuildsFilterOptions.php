<?php

namespace App\Http\Controllers\PublicPortal\Concerns;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyType;
use App\Models\Company;
use App\Models\Property;

/**
 * Builds the dynamic filter option lists (price bounds, neighborhoods,
 * cities, features, filterable custom attributes) for a company's public
 * properties. Shared by the listing and advanced-search pages so both
 * render the exact same set of controls.
 */
trait BuildsFilterOptions
{
    /**
     * @return array<string, mixed>
     */
    private function filterOptions(Company $company): array
    {
        $publicProperties = Property::public()->where('company_id', $company->id);

        return [
            'purposes' => array_map(
                fn (PropertyPurpose $purpose): array => ['value' => $purpose->value, 'label' => $purpose->label()],
                PropertyPurpose::cases(),
            ),
            'types' => array_map(
                fn (PropertyType $type): array => ['value' => $type->value, 'label' => $type->label()],
                PropertyType::cases(),
            ),
            'neighborhoods' => (clone $publicProperties)->distinct()->orderBy('neighborhood')->pluck('neighborhood'),
            'cities' => (clone $publicProperties)->distinct()->orderBy('city')->pluck('city'),
            // Laravel's min()/max() aggregate helpers strip custom select clauses, which would
            // drop the selectRaw() alias from withPrincipalPrice() — pluck + Collection::min/max instead.
            'priceRange' => (function () use ($publicProperties): array {
                $prices = (clone $publicProperties)->withPrincipalPrice()->pluck('principal_price');

                return ['min' => $prices->min(), 'max' => $prices->max()];
            })(),
            'featureCategories' => $company->featureCategories()
                ->where('active', true)
                ->with(['features' => fn ($query) => $query->where('active', true)->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name']),
            'attributes' => $company->propertyAttributes()
                ->where('filterable', true)
                ->with('options:id,property_attribute_id,value')
                ->orderBy('name')
                ->get(),
        ];
    }
}
