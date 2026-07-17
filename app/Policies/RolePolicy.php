<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view the list of roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('papeis.visualizar');
    }

    /**
     * Determine whether the user can view the given role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->company_id === $role->getAttribute('company_id') && $user->can('papeis.visualizar');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('papeis.criar');
    }

    /**
     * Determine whether the user can update the given role.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->company_id === $role->getAttribute('company_id') && $user->can('papeis.editar');
    }

    /**
     * Determine whether the user can delete the given role.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->company_id === $role->getAttribute('company_id') && $user->can('papeis.excluir');
    }
}
