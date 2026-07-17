<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\CompanyOnboardingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the company onboarding page.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $company = $request->user()->company;

        if ($company->onboarded_at) {
            return to_route('dashboard');
        }

        return Inertia::render('onboarding/company', [
            'company' => $company->only('name', 'document', 'phone', 'address'),
        ]);
    }

    /**
     * Complete the company onboarding.
     */
    public function update(CompanyOnboardingRequest $request): RedirectResponse
    {
        $company = $request->user()->company;

        $company->fill($request->validated());
        $company->onboarded_at = now();
        $company->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Empresa configurada com sucesso.')]);

        return to_route('dashboard');
    }
}
