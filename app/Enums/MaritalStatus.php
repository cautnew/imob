<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case Single = 'solteiro';
    case Married = 'casado';
    case Divorced = 'divorciado';
    case Widowed = 'viuvo';
    case CommonLawUnion = 'uniao_estavel';

    /**
     * Get the human-readable label for the marital status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Single => 'Solteiro(a)',
            self::Married => 'Casado(a)',
            self::Divorced => 'Divorciado(a)',
            self::Widowed => 'Viúvo(a)',
            self::CommonLawUnion => 'União estável',
        };
    }
}
