<?php

namespace App\Http\Controllers\PublicPortal;

use App\Enums\PropertyAttributeType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicPortal\Concerns\BuildsFilterOptions;
use App\Http\Controllers\PublicPortal\Concerns\ManagesVisitorCookies;
use App\Models\Company;
use App\Models\Property;
use App\Models\PropertyAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    use BuildsFilterOptions, ManagesVisitorCookies;

    /**
     * List the company's public properties, filtered dynamically.
     */
    public function index(Request $request): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $query = Property::public()
            ->where('company_id', $company->id)
            ->withPrincipalPrice()
            ->with(['media' => fn ($q) => $q->where('is_cover', true), 'features', 'prices.priceType']);

        $this->applyFilters($query, $request, $company);
        $this->applySort($query, $request);

        $properties = $query->paginate(12)->withQueryString();

        $favoriteIds = $this->idsFor($this->readCookie($request, 'portal_favorites'), $company->slug);
        $comparisonIds = $this->idsFor($this->readCookie($request, 'portal_comparison'), $company->slug);

        return view('public-portal.properties.index', [
            'company' => $company,
            'properties' => $properties,
            'filters' => $this->filterOptions($company),
            'selected' => $this->selectedFilters($request),
            'favoriteIds' => $favoriteIds,
            'comparisonIds' => $comparisonIds,
        ]);
    }

    /**
     * Show a single public property.
     */
    public function show(Request $request, string $companySlug, string $propertySlug): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $property = Property::public()
            ->where('company_id', $company->id)
            ->where('slug', $propertySlug)
            ->with([
                'media', 'features.featureCategory',
                'attributeValues.propertyAttribute', 'attributeValues.propertyAttributeOption',
                'prices.priceType',
            ])
            ->firstOrFail();

        $favoriteIds = $this->idsFor($this->readCookie($request, 'portal_favorites'), $company->slug);
        $comparisonIds = $this->idsFor($this->readCookie($request, 'portal_comparison'), $company->slug);

        return view('public-portal.properties.show', [
            'company' => $company,
            'property' => $property,
            'favoriteIds' => $favoriteIds,
            'comparisonIds' => $comparisonIds,
        ]);
    }

    private function applyFilters(Builder $query, Request $request, Company $company): void
    {
        if ($purpose = $request->string('purpose')->value()) {
            $query->where('purpose', $purpose);
        }

        if ($types = array_filter((array) $request->input('tipo', []))) {
            $query->whereIn('type', $types);
        }

        if ($neighborhoods = array_filter((array) $request->input('bairro', []))) {
            $query->whereIn('neighborhood', $neighborhoods);
        }

        if ($cities = array_filter((array) $request->input('cidade', []))) {
            $query->whereIn('city', $cities);
        }

        if ($request->filled('preco_min') || $request->filled('preco_max')) {
            $query->principalPriceBetween(
                $request->filled('preco_min') ? $request->float('preco_min') : null,
                $request->filled('preco_max') ? $request->float('preco_max') : null,
            );
        }

        foreach (array_filter((array) $request->input('caracteristicas', [])) as $featureId) {
            $query->whereHas('features', fn (Builder $q) => $q->where('features.id', (int) $featureId));
        }

        $this->applyAttributeFilters($query, $request, $company);
    }

    private function applyAttributeFilters(Builder $query, Request $request, Company $company): void
    {
        $attributeInputs = (array) $request->input('atributos', []);

        if ($attributeInputs === []) {
            return;
        }

        $attributes = $company->propertyAttributes()
            ->where('filterable', true)
            ->whereIn('id', array_keys($attributeInputs))
            ->get()
            ->keyBy('id');

        foreach ($attributeInputs as $attributeId => $value) {
            $attribute = $attributes->get((int) $attributeId);

            if (! $attribute || $value === null || $value === '' || $value === []) {
                continue;
            }

            $this->applyAttributeFilter($query, $attribute, $value);
        }
    }

    private function applyAttributeFilter(Builder $query, PropertyAttribute $attribute, mixed $value): void
    {
        match ($attribute->type) {
            PropertyAttributeType::Text => $query->whereHas(
                'attributeValues',
                fn (Builder $q) => $q->where('property_attribute_id', $attribute->id)
                    ->where('value', 'like', '%'.$value.'%'),
            ),
            PropertyAttributeType::Integer, PropertyAttributeType::Decimal => $query->whereHas(
                'attributeValues',
                function (Builder $q) use ($attribute, $value) {
                    $q->where('property_attribute_id', $attribute->id);

                    if (isset($value['min']) && $value['min'] !== '') {
                        $q->whereRaw('CAST(value AS DECIMAL(12,2)) >= ?', [$value['min']]);
                    }

                    if (isset($value['max']) && $value['max'] !== '') {
                        $q->whereRaw('CAST(value AS DECIMAL(12,2)) <= ?', [$value['max']]);
                    }
                },
            ),
            PropertyAttributeType::Boolean => $query->whereHas(
                'attributeValues',
                fn (Builder $q) => $q->where('property_attribute_id', $attribute->id)->where('value', '1'),
            ),
            PropertyAttributeType::Date => $query->whereHas(
                'attributeValues',
                function (Builder $q) use ($attribute, $value) {
                    $q->where('property_attribute_id', $attribute->id);

                    if (! empty($value['de'])) {
                        $q->where('value', '>=', $value['de']);
                    }

                    if (! empty($value['ate'])) {
                        $q->where('value', '<=', $value['ate']);
                    }
                },
            ),
            PropertyAttributeType::Select => $query->whereHas(
                'attributeValues',
                fn (Builder $q) => $q->where('property_attribute_id', $attribute->id)
                    ->where('property_attribute_option_id', $value),
            ),
            PropertyAttributeType::Multiselect => collect((array) $value)->each(
                fn ($optionId) => $query->whereHas(
                    'attributeValues',
                    fn (Builder $q) => $q->where('property_attribute_id', $attribute->id)
                        ->where('property_attribute_option_id', $optionId),
                ),
            ),
        };
    }

    private function applySort(Builder $query, Request $request): void
    {
        match ($request->string('ordenar')->value()) {
            'preco_asc' => $query->orderByPrincipalPrice('asc'),
            'preco_desc' => $query->orderByPrincipalPrice('desc'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function selectedFilters(Request $request): array
    {
        return $request->only([
            'purpose', 'tipo', 'bairro', 'cidade', 'preco_min', 'preco_max', 'caracteristicas', 'atributos', 'ordenar',
        ]);
    }
}
