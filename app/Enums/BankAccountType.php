<?php

namespace App\Enums;

enum BankAccountType: string
{
    case Checking = 'corrente';
    case Savings = 'poupanca';

    /**
     * Get the human-readable label for the bank account type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta corrente',
            self::Savings => 'Poupança',
        };
    }
}
