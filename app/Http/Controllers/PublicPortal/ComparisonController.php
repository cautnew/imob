<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicPortal\Concerns\ManagesVisitorCookies;
use App\Models\Company;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    use ManagesVisitorCookies;

    private const COOKIE = 'portal_comparison';

    /**
     * Show the comparison table for the visitor's selected properties.
     */
    public function index(Request $request): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $ids = $this->idsFor($this->readCookie($request, self::COOKIE), $company->slug);

        $properties = Property::public()
            ->where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->with([
                'media' => fn ($q) => $q->where('is_cover', true),
                'features.featureCategory', 'attributeValues.propertyAttribute', 'attributeValues.propertyAttributeOption',
                'prices.priceType',
            ])
            ->get();

        $comparableAttributes = $company->propertyAttributes()->where('comparable', true)->orderBy('name')->get();
        $comparablePriceTypes = $company->priceTypes()->where('comparable', true)->orderBy('name')->get();

        $comparisonFeatures = $properties->pluck('features')->flatten()
            ->unique('id')
            ->sortBy([['featureCategory.name', 'asc'], ['name', 'asc']])
            ->values();

        return view('public-portal.comparison.index', [
            'company' => $company,
            'properties' => $properties,
            'comparisonIds' => $ids,
            'comparableAttributes' => $comparableAttributes,
            'comparablePriceTypes' => $comparablePriceTypes,
            'comparisonFeatures' => $comparisonFeatures,
        ]);
    }

    public function store(Request $request, string $companySlug, string $propertySlug): RedirectResponse
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $property = Property::public()->where('company_id', $company->id)->where('slug', $propertySlug)->firstOrFail();

        $cookieData = $this->readCookie($request, self::COOKIE);
        $ids = $this->idsFor($cookieData, $company->slug);

        $max = (int) config('public-portal.comparison_max');

        if (! in_array($property->id, $ids, true) && count($ids) >= $max) {
            return back()->with('comparison_error', __('Você já atingiu o limite de :max imóveis para comparação.', ['max' => $max]));
        }

        $ids[] = $property->id;
        $cookieData[$company->slug] = array_values(array_unique($ids));

        $this->writeCookie(self::COOKIE, $cookieData, 60 * 24);

        return back();
    }

    public function destroy(Request $request, string $companySlug, string $propertySlug): RedirectResponse
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $property = Property::public()->where('company_id', $company->id)->where('slug', $propertySlug)->firstOrFail();

        $cookieData = $this->readCookie($request, self::COOKIE);
        $ids = array_values(array_diff($this->idsFor($cookieData, $company->slug), [$property->id]));
        $cookieData[$company->slug] = $ids;

        $this->writeCookie(self::COOKIE, $cookieData, 60 * 24);

        return back();
    }
}
