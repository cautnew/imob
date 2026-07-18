<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Property;
use App\Models\Scopes\CompanyScope;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Build the public sitemap across every company's public properties.
     *
     * Deliberately bypasses the `public.tenant` middleware (this route
     * enumerates all companies), so it explicitly drops CompanyScope rather
     * than relying on a bound `currentCompany`.
     */
    public function index(): Response
    {
        $urls = [];

        Company::query()->orderBy('id')->cursor()->each(function (Company $company) use (&$urls): void {
            $urls[] = ['loc' => url($company->slug), 'lastmod' => $company->updated_at];

            Property::withoutGlobalScope(CompanyScope::class)
                ->public()
                ->where('company_id', $company->id)
                ->select('slug', 'updated_at')
                ->chunk(500, function ($properties) use (&$urls, $company): void {
                    foreach ($properties as $property) {
                        $urls[] = [
                            'loc' => url("{$company->slug}/imoveis/{$property->slug}"),
                            'lastmod' => $property->updated_at,
                        ];
                    }
                });
        });

        return response()
            ->view('public-portal.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
