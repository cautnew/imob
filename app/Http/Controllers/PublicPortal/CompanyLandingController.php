<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Property;
use Illuminate\View\View;

class CompanyLandingController extends Controller
{
    /**
     * Show the company's public landing page.
     */
    public function show(): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        $featuredProperties = Property::public()
            ->where('company_id', $company->id)
            ->with(['media' => fn ($query) => $query->where('is_cover', true), 'prices.priceType'])
            ->latest()
            ->take(6)
            ->get();

        return view('public-portal.company.show', [
            'company' => $company,
            'featuredProperties' => $featuredProperties,
        ]);
    }
}
