<?php

namespace App\Enums;

enum PropertyAttributeType: string
{
    case Text = 'texto';
    case Integer = 'inteiro';
    case Decimal = 'decimal';
    case Boolean = 'boolean';
    case Date = 'data';
    case Select = 'select';
    case Multiselect = 'multiselect';

    /**
     * Determine whether this type stores a list of predefined options.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Multiselect], true);
    }

    /**
     * Get the human-readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texto',
            self::Integer => 'Número inteiro',
            self::Decimal => 'Número decimal',
            self::Boolean => 'Sim/Não',
            self::Date => 'Data',
            self::Select => 'Lista de opções (escolha única)',
            self::Multiselect => 'Lista de opções (múltipla escolha)',
        };
    }
}
