<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy
{
    /**
     * Determine whether the user can view the list of leases.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('locacoes.visualizar');
    }

    /**
     * Determine whether the user can view the given lease.
     */
    public function view(User $user, Lease $lease): bool
    {
        return $user->company_id === $lease->company_id && $user->can('locacoes.visualizar');
    }

    /**
     * Determine whether the user can create leases.
     */
    public function create(User $user): bool
    {
        return $user->can('locacoes.criar');
    }

    /**
     * Determine whether the user can update the given lease.
     */
    public function update(User $user, Lease $lease): bool
    {
        return $user->company_id === $lease->company_id && $user->can('locacoes.editar');
    }

    /**
     * Determine whether the user can delete the given lease.
     */
    public function delete(User $user, Lease $lease): bool
    {
        return $user->company_id === $lease->company_id && $user->can('locacoes.excluir');
    }
}
