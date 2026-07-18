<?php

namespace App\Enums;

enum BillReceiptStatus: string
{
    case Pending = 'aguardando_aprovacao';
    case Approved = 'aprovado';
    case Rejected = 'rejeitado';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando aprovação',
            self::Approved => 'Aprovado',
            self::Rejected => 'Rejeitado',
        };
    }
}
