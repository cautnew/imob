<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine whether the user can view the list of properties.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('imoveis.visualizar');
    }

    /**
     * Determine whether the user can view the given property.
     */
    public function view(User $user, Property $property): bool
    {
        return $user->company_id === $property->company_id && $user->can('imoveis.visualizar');
    }

    /**
     * Determine whether the user can create properties.
     */
    public function create(User $user): bool
    {
        return $user->can('imoveis.criar');
    }

    /**
     * Determine whether the user can update the given property.
     */
    public function update(User $user, Property $property): bool
    {
        return $user->company_id === $property->company_id && $user->can('imoveis.editar');
    }

    /**
     * Determine whether the user can delete the given property.
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->company_id === $property->company_id && $user->can('imoveis.excluir');
    }
}
