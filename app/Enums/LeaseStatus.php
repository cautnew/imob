<?php

namespace App\Enums;

enum LeaseStatus: string
{
    case Active = 'ativo';
    case Defaulting = 'inadimplente';
    case Terminated = 'encerrado';
    case Cancelled = 'cancelado';
    case Expired = 'vencido';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Defaulting => 'Inadimplente',
            self::Terminated => 'Encerrado',
            self::Cancelled => 'Cancelado',
            self::Expired => 'Vencido',
        };
    }
}
