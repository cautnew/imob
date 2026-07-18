<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanySlugUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the company's public portal settings page.
     */
    public function edit(Request $request): Response
    {
        abort_unless($request->user()->is_owner, 403);

        return Inertia::render('settings/company', [
            'company' => $request->user()->company->only('id', 'name', 'slug'),
        ]);
    }

    /**
     * Update the company's public portal slug.
     */
    public function update(CompanySlugUpdateRequest $request): RedirectResponse
    {
        $request->user()->company->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Endereço do portal público atualizado com sucesso.')]);

        return to_route('company.edit');
    }
}
