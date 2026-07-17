<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view the list of permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('permissoes.visualizar');
    }
}
