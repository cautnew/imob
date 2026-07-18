<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'receita';
    case Expense = 'despesa';

    /**
     * Get the human-readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Income => 'Receita',
            self::Expense => 'Despesa',
        };
    }
}
