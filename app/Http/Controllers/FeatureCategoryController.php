<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeatureCategoryStoreRequest;
use App\Http\Requests\FeatureCategoryUpdateRequest;
use App\Models\FeatureCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeatureCategoryController extends Controller
{
    /**
     * List the feature categories of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FeatureCategory::class);

        return Inertia::render('feature-categories/index', [
            'featureCategories' => $request->user()->company->featureCategories()
                ->withCount('features')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form to create a new feature category.
     */
    public function create(): Response
    {
        $this->authorize('create', FeatureCategory::class);

        return Inertia::render('feature-categories/create');
    }

    /**
     * Store a newly created feature category.
     */
    public function store(FeatureCategoryStoreRequest $request): RedirectResponse
    {
        FeatureCategory::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria criada com sucesso.')]);

        return to_route('feature-categories.index');
    }

    /**
     * Show the form to edit an existing feature category.
     */
    public function edit(FeatureCategory $featureCategory): Response
    {
        $this->authorize('update', $featureCategory);

        return Inertia::render('feature-categories/edit', [
            'featureCategory' => $featureCategory->only('id', 'name', 'active'),
        ]);
    }

    /**
     * Update an existing feature category.
     */
    public function update(FeatureCategoryUpdateRequest $request, FeatureCategory $featureCategory): RedirectResponse
    {
        $featureCategory->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria atualizada com sucesso.')]);

        return to_route('feature-categories.index');
    }

    /**
     * Toggle the active status of an existing feature category.
     */
    public function toggle(FeatureCategory $featureCategory): RedirectResponse
    {
        $this->authorize('update', $featureCategory);

        $featureCategory->update(['active' => ! $featureCategory->active]);

        Inertia::flash('toast', ['type' => 'success', 'message' => $featureCategory->active
            ? __('Categoria ativada com sucesso.')
            : __('Categoria desativada com sucesso.'),
        ]);

        return to_route('feature-categories.index');
    }

    /**
     * Delete an existing feature category.
     */
    public function destroy(FeatureCategory $featureCategory): RedirectResponse
    {
        $this->authorize('delete', $featureCategory);

        abort_if($featureCategory->features()->exists(), 422, __('Não é possível excluir uma categoria com características vinculadas.'));

        $featureCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria removida com sucesso.')]);

        return to_route('feature-categories.index');
    }
}
