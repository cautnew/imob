<?php

namespace App\Policies;

use App\Models\Owner;
use App\Models\User;

class OwnerPolicy
{
    /**
     * Determine whether the user can view the list of owners.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('proprietarios.visualizar');
    }

    /**
     * Determine whether the user can view the given owner.
     */
    public function view(User $user, Owner $owner): bool
    {
        return $user->company_id === $owner->company_id && $user->can('proprietarios.visualizar');
    }

    /**
     * Determine whether the user can create owners.
     */
    public function create(User $user): bool
    {
        return $user->can('proprietarios.criar');
    }

    /**
     * Determine whether the user can update the given owner.
     */
    public function update(User $user, Owner $owner): bool
    {
        return $user->company_id === $owner->company_id && $user->can('proprietarios.editar');
    }

    /**
     * Determine whether the user can delete the given owner.
     */
    public function delete(User $user, Owner $owner): bool
    {
        return $user->company_id === $owner->company_id && $user->can('proprietarios.excluir');
    }
}
