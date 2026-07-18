<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    /**
     * Determine whether the user can view the list of bills.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('boletos.visualizar');
    }

    /**
     * Determine whether the user can view the given bill.
     */
    public function view(User $user, Bill $bill): bool
    {
        return $user->company_id === $bill->company_id && $user->can('boletos.visualizar');
    }

    /**
     * Determine whether the user can create bills.
     */
    public function create(User $user): bool
    {
        return $user->can('boletos.criar');
    }

    /**
     * Determine whether the user can update the given bill.
     */
    public function update(User $user, Bill $bill): bool
    {
        return $user->company_id === $bill->company_id && $user->can('boletos.editar');
    }

    /**
     * Determine whether the user can delete the given bill.
     */
    public function delete(User $user, Bill $bill): bool
    {
        return $user->company_id === $bill->company_id && $user->can('boletos.excluir');
    }
}
