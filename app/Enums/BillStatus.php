<?php

namespace App\Enums;

enum BillStatus: string
{
    case Pending = 'pendente';
    case Paid = 'pago';
    case Overdue = 'vencido';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::Overdue => 'Vencido',
        };
    }
}
