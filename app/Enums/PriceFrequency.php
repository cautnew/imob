<?php

namespace App\Enums;

enum PriceFrequency: string
{
    case OneTime = 'unico';
    case Daily = 'diario';
    case Weekly = 'semanal';
    case Monthly = 'mensal';
    case Quarterly = 'trimestral';
    case SemiAnnual = 'semestral';
    case Annual = 'anual';

    /**
     * Get the human-readable label for the frequency.
     */
    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'Único',
            self::Daily => 'Diário',
            self::Weekly => 'Semanal',
            self::Monthly => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::SemiAnnual => 'Semestral',
            self::Annual => 'Anual',
        };
    }
}
