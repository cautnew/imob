<?php

namespace App\Policies;

use App\Models\Lessee;
use App\Models\User;

class LesseePolicy
{
    /**
     * Determine whether the user can view the list of lessees.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inquilinos.visualizar');
    }

    /**
     * Determine whether the user can view the given lessee.
     */
    public function view(User $user, Lessee $lessee): bool
    {
        return $user->company_id === $lessee->company_id && $user->can('inquilinos.visualizar');
    }

    /**
     * Determine whether the user can create lessees.
     */
    public function create(User $user): bool
    {
        return $user->can('inquilinos.criar');
    }

    /**
     * Determine whether the user can update the given lessee.
     */
    public function update(User $user, Lessee $lessee): bool
    {
        return $user->company_id === $lessee->company_id && $user->can('inquilinos.editar');
    }

    /**
     * Determine whether the user can delete the given lessee.
     */
    public function delete(User $user, Lessee $lessee): bool
    {
        return $user->company_id === $lessee->company_id && $user->can('inquilinos.excluir');
    }
}
