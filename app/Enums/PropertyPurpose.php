<?php

namespace App\Enums;

enum PropertyPurpose: string
{
    case Sale = 'venda';
    case Rent = 'aluguel';
    case SaleAndRent = 'venda_aluguel';

    /**
     * Determine whether this purpose requires a sale price.
     */
    public function requiresSalePrice(): bool
    {
        return in_array($this, [self::Sale, self::SaleAndRent], true);
    }

    /**
     * Determine whether this purpose requires a rent price.
     */
    public function requiresRentPrice(): bool
    {
        return in_array($this, [self::Rent, self::SaleAndRent], true);
    }

    /**
     * Get the human-readable label for the purpose.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Venda',
            self::Rent => 'Aluguel',
            self::SaleAndRent => 'Venda e aluguel',
        };
    }
}
