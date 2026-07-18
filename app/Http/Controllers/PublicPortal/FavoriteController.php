<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicPortal\Concerns\ManagesVisitorCookies;
use App\Models\Company;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    use ManagesVisitorCookies;

    private const COOKIE = 'portal_favorites';

    /**
     * List the visitor's favorited properties for this company.
     */
    public function index(Request $request): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $ids = $this->idsFor($this->readCookie($request, self::COOKIE), $company->slug);

        $properties = Property::public()
            ->where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->with(['media' => fn ($q) => $q->where('is_cover', true), 'prices.priceType'])
            ->get();

        return view('public-portal.favorites.index', [
            'company' => $company,
            'properties' => $properties,
            'favoriteIds' => $ids,
        ]);
    }

    public function store(Request $request, string $companySlug, string $propertySlug): RedirectResponse
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $property = Property::public()->where('company_id', $company->id)->where('slug', $propertySlug)->firstOrFail();

        $cookieData = $this->readCookie($request, self::COOKIE);
        $ids = $this->idsFor($cookieData, $company->slug);
        $ids[] = $property->id;
        $cookieData[$company->slug] = array_values(array_unique($ids));

        $this->writeCookie(self::COOKIE, $cookieData, 60 * 24 * (int) config('public-portal.favorites_cookie_ttl_days'));

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

        $this->writeCookie(self::COOKIE, $cookieData, 60 * 24 * (int) config('public-portal.favorites_cookie_ttl_days'));

        return back();
    }
}
