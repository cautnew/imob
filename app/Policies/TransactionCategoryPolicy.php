<?php

namespace App\Policies;

use App\Models\TransactionCategory;
use App\Models\User;

class TransactionCategoryPolicy
{
    /**
     * Determine whether the user can view the list of transaction categories.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('financeiro.visualizar');
    }

    /**
     * Determine whether the user can view the given transaction category.
     */
    public function view(User $user, TransactionCategory $transactionCategory): bool
    {
        return $user->company_id === $transactionCategory->company_id && $user->can('financeiro.visualizar');
    }

    /**
     * Determine whether the user can create transaction categories.
     */
    public function create(User $user): bool
    {
        return $user->can('financeiro.criar');
    }

    /**
     * Determine whether the user can update the given transaction category.
     */
    public function update(User $user, TransactionCategory $transactionCategory): bool
    {
        return $user->company_id === $transactionCategory->company_id && $user->can('financeiro.editar');
    }

    /**
     * Determine whether the user can delete the given transaction category.
     */
    public function delete(User $user, TransactionCategory $transactionCategory): bool
    {
        return $user->company_id === $transactionCategory->company_id && $user->can('financeiro.excluir');
    }
}
