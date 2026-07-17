<?php

namespace App\Policies;

use App\Models\PropertyAttribute;
use App\Models\User;

class PropertyAttributePolicy
{
    /**
     * Determine whether the user can view the list of property attributes.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('atributos.visualizar');
    }

    /**
     * Determine whether the user can view the given property attribute.
     */
    public function view(User $user, PropertyAttribute $propertyAttribute): bool
    {
        return $user->company_id === $propertyAttribute->company_id && $user->can('atributos.visualizar');
    }

    /**
     * Determine whether the user can create property attributes.
     */
    public function create(User $user): bool
    {
        return $user->can('atributos.criar');
    }

    /**
     * Determine whether the user can update the given property attribute.
     */
    public function update(User $user, PropertyAttribute $propertyAttribute): bool
    {
        return $user->company_id === $propertyAttribute->company_id && $user->can('atributos.editar');
    }

    /**
     * Determine whether the user can delete the given property attribute.
     */
    public function delete(User $user, PropertyAttribute $propertyAttribute): bool
    {
        return $user->company_id === $propertyAttribute->company_id && $user->can('atributos.excluir');
    }
}
