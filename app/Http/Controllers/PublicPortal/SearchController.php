<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicPortal\Concerns\BuildsFilterOptions;
use App\Models\Company;
use Illuminate\View\View;

class SearchController extends Controller
{
    use BuildsFilterOptions;

    /**
     * Show the advanced search form. Submits (GET) into properties.index.
     */
    public function show(): View
    {
        /** @var Company $company */
        $company = app('currentCompany');

        return view('public-portal.properties.search', [
            'company' => $company,
            'filters' => $this->filterOptions($company),
        ]);
    }
}
