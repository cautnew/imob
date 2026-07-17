<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the list of users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('usuarios.visualizar');
    }

    /**
     * Determine whether the user can view the target user.
     */
    public function view(User $user, User $target): bool
    {
        return $user->company_id === $target->company_id && $user->can('usuarios.visualizar');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('usuarios.criar');
    }

    /**
     * Determine whether the user can update the target user.
     */
    public function update(User $user, User $target): bool
    {
        return $user->company_id === $target->company_id && $user->can('usuarios.editar');
    }

    /**
     * Determine whether the user can delete the target user.
     */
    public function delete(User $user, User $target): bool
    {
        if ($user->company_id !== $target->company_id) {
            return false;
        }

        if ($target->is_owner) {
            return false;
        }

        if ($user->id === $target->id) {
            return false;
        }

        return $user->can('usuarios.excluir');
    }
}
