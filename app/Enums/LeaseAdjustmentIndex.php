<?php

namespace App\Enums;

enum LeaseAdjustmentIndex: string
{
    case Igpm = 'igpm';
    case Ipca = 'ipca';
    case Incc = 'incc';
    case Fixed = 'fixo';
    case Other = 'outro';

    /**
     * Get the human-readable label for the adjustment index.
     */
    public function label(): string
    {
        return match ($this) {
            self::Igpm => 'IGP-M',
            self::Ipca => 'IPCA',
            self::Incc => 'INCC',
            self::Fixed => 'Percentual fixo',
            self::Other => 'Outro',
        };
    }
}
