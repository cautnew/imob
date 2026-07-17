<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeatureStoreRequest;
use App\Http\Requests\FeatureUpdateRequest;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeatureController extends Controller
{
    /**
     * List the features of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Feature::class);

        $featureCategoryId = $request->integer('feature_category_id') ?: null;

        return Inertia::render('features/index', [
            'features' => $request->user()->company->features()
                ->with('featureCategory:id,name')
                ->when($featureCategoryId, fn ($query, $id) => $query->where('feature_category_id', $id))
                ->orderBy('name')
                ->get(),
            'featureCategories' => $request->user()->company->featureCategories()
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedCategoryId' => $featureCategoryId,
        ]);
    }

    /**
     * Show the form to create a new feature.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Feature::class);

        return Inertia::render('features/create', [
            'featureCategories' => $request->user()->company->featureCategories()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedCategoryId' => $request->integer('feature_category_id') ?: null,
        ]);
    }

    /**
     * Store a newly created feature.
     */
    public function store(FeatureStoreRequest $request): RedirectResponse
    {
        Feature::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Característica criada com sucesso.')]);

        return to_route('features.index');
    }

    /**
     * Show the form to edit an existing feature.
     */
    public function edit(Request $request, Feature $feature): Response
    {
        $this->authorize('update', $feature);

        return Inertia::render('features/edit', [
            'feature' => $feature->only('id', 'name', 'active', 'feature_category_id'),
            'featureCategories' => $request->user()->company->featureCategories()
                ->where(fn ($query) => $query
                    ->where('active', true)
                    ->orWhere('id', $feature->feature_category_id))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Update an existing feature.
     */
    public function update(FeatureUpdateRequest $request, Feature $feature): RedirectResponse
    {
        $feature->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Característica atualizada com sucesso.')]);

        return to_route('features.index');
    }

    /**
     * Toggle the active status of an existing feature.
     */
    public function toggle(Feature $feature): RedirectResponse
    {
        $this->authorize('update', $feature);

        $feature->update(['active' => ! $feature->active]);

        Inertia::flash('toast', ['type' => 'success', 'message' => $feature->active
            ? __('Característica ativada com sucesso.')
            : __('Característica desativada com sucesso.'),
        ]);

        return to_route('features.index');
    }

    /**
     * Delete an existing feature.
     */
    public function destroy(Feature $feature): RedirectResponse
    {
        $this->authorize('delete', $feature);

        $feature->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Característica removida com sucesso.')]);

        return to_route('features.index');
    }
}
