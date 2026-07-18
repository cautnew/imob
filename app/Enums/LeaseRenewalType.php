<?php

namespace App\Enums;

enum LeaseRenewalType: string
{
    case Automatic = 'automatica';
    case Manual = 'manual';
    case None = 'nenhuma';

    /**
     * Get the human-readable label for the renewal type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Automatic => 'Automática',
            self::Manual => 'Manual',
            self::None => 'Sem renovação',
        };
    }
}
