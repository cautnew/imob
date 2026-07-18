<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant for the public property portal from the
 * `{companySlug}` route segment, rather than from the authenticated user
 * (see ResolveCurrentTenant). Binding `currentCompany` here transparently
 * scopes Property/Feature/PropertyAttribute/PriceType queries via
 * CompanyScope with no further changes to those models.
 */
class ResolvePublicTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = Company::query()->where('slug', $request->route('companySlug'))->first();

        abort_unless($company, 404);

        app()->instance('currentCompany', $company);

        return $next($request);
    }
}
