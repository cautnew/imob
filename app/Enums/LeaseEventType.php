<?php

namespace App\Enums;

enum LeaseEventType: string
{
    case Created = 'criado';
    case Adjusted = 'reajustado';
    case Renewed = 'renovado';
    case StatusChanged = 'situacao_alterada';
    case DocumentAttached = 'documento_anexado';
    case DocumentRemoved = 'documento_removido';
    case Note = 'nota';

    /**
     * Get the human-readable label for the event type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Created => 'Contrato criado',
            self::Adjusted => 'Reajuste aplicado',
            self::Renewed => 'Contrato renovado',
            self::StatusChanged => 'Situação alterada',
            self::DocumentAttached => 'Documento anexado',
            self::DocumentRemoved => 'Documento removido',
            self::Note => 'Observação',
        };
    }
}
