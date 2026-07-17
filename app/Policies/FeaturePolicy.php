<?php

namespace App\Policies;

use App\Models\Feature;
use App\Models\User;

class FeaturePolicy
{
    /**
     * Determine whether the user can view the list of features.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('caracteristicas.visualizar');
    }

    /**
     * Determine whether the user can view the given feature.
     */
    public function view(User $user, Feature $feature): bool
    {
        return $user->company_id === $feature->company_id && $user->can('caracteristicas.visualizar');
    }

    /**
     * Determine whether the user can create features.
     */
    public function create(User $user): bool
    {
        return $user->can('caracteristicas.criar');
    }

    /**
     * Determine whether the user can update the given feature.
     */
    public function update(User $user, Feature $feature): bool
    {
        return $user->company_id === $feature->company_id && $user->can('caracteristicas.editar');
    }

    /**
     * Determine whether the user can delete the given feature.
     */
    public function delete(User $user, Feature $feature): bool
    {
        return $user->company_id === $feature->company_id && $user->can('caracteristicas.excluir');
    }
}
