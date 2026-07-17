<?php

namespace App\Policies;

use App\Models\FeatureCategory;
use App\Models\User;

class FeatureCategoryPolicy
{
    /**
     * Determine whether the user can view the list of feature categories.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('caracteristicas.visualizar');
    }

    /**
     * Determine whether the user can view the given feature category.
     */
    public function view(User $user, FeatureCategory $featureCategory): bool
    {
        return $user->company_id === $featureCategory->company_id && $user->can('caracteristicas.visualizar');
    }

    /**
     * Determine whether the user can create feature categories.
     */
    public function create(User $user): bool
    {
        return $user->can('caracteristicas.criar');
    }

    /**
     * Determine whether the user can update the given feature category.
     */
    public function update(User $user, FeatureCategory $featureCategory): bool
    {
        return $user->company_id === $featureCategory->company_id && $user->can('caracteristicas.editar');
    }

    /**
     * Determine whether the user can delete the given feature category.
     */
    public function delete(User $user, FeatureCategory $featureCategory): bool
    {
        return $user->company_id === $featureCategory->company_id && $user->can('caracteristicas.excluir');
    }
}
