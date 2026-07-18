<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pendente';
    case Paid = 'pago';
    case Overdue = 'vencido';
    case AwaitingApproval = 'aguardando_aprovacao';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::Overdue => 'Vencido',
            self::AwaitingApproval => 'Aguardando aprovação',
        };
    }
}
