<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view the list of transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('financeiro.visualizar');
    }

    /**
     * Determine whether the user can view the given transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->company_id === $transaction->company_id && $user->can('financeiro.visualizar');
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        return $user->can('financeiro.criar');
    }

    /**
     * Determine whether the user can update the given transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->company_id === $transaction->company_id && $user->can('financeiro.editar');
    }

    /**
     * Determine whether the user can delete the given transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->company_id === $transaction->company_id && $user->can('financeiro.excluir');
    }
}
