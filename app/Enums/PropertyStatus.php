<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Available = 'disponivel';
    case Reserved = 'reservado';
    case Sold = 'vendido';
    case Rented = 'alugado';
    case Inactive = 'inativo';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponível',
            self::Reserved => 'Reservado',
            self::Sold => 'Vendido',
            self::Rented => 'Alugado',
            self::Inactive => 'Inativo',
        };
    }
}
