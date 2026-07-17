<?php

namespace App\Policies;

use App\Models\PriceType;
use App\Models\User;

class PriceTypePolicy
{
    /**
     * Determine whether the user can view the list of price types.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('precos.visualizar');
    }

    /**
     * Determine whether the user can view the given price type.
     */
    public function view(User $user, PriceType $priceType): bool
    {
        return $user->company_id === $priceType->company_id && $user->can('precos.visualizar');
    }

    /**
     * Determine whether the user can create price types.
     */
    public function create(User $user): bool
    {
        return $user->can('precos.criar');
    }

    /**
     * Determine whether the user can update the given price type.
     */
    public function update(User $user, PriceType $priceType): bool
    {
        return $user->company_id === $priceType->company_id && $user->can('precos.editar');
    }

    /**
     * Determine whether the user can delete the given price type.
     */
    public function delete(User $user, PriceType $priceType): bool
    {
        return $user->company_id === $priceType->company_id && $user->can('precos.excluir');
    }
}
