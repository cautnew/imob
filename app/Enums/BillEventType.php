<?php

namespace App\Enums;

enum BillEventType: string
{
    case Created = 'criado';
    case PdfUploaded = 'pdf_anexado';
    case PdfReplaced = 'pdf_atualizado';
    case TransactionAttached = 'lancamento_vinculado';
    case TransactionDetached = 'lancamento_desvinculado';
    case MarkedAsPaid = 'marcado_como_pago';
    case Reopened = 'reaberto';

    /**
     * Get the human-readable label for the event type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Created => 'Boleto criado',
            self::PdfUploaded => 'PDF anexado',
            self::PdfReplaced => 'PDF atualizado',
            self::TransactionAttached => 'Lançamento vinculado',
            self::TransactionDetached => 'Lançamento desvinculado',
            self::MarkedAsPaid => 'Marcado como pago',
            self::Reopened => 'Reaberto',
        };
    }
}
