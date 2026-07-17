<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartamento';
    case House = 'casa';
    case Penthouse = 'cobertura';
    case Land = 'terreno';
    case CommercialRoom = 'sala_comercial';
    case Store = 'loja';
    case Warehouse = 'galpao';
    case Farm = 'chacara_sitio';
    case Other = 'outro';

    /**
     * Get the human-readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Apartment => 'Apartamento',
            self::House => 'Casa',
            self::Penthouse => 'Cobertura',
            self::Land => 'Terreno',
            self::CommercialRoom => 'Sala comercial',
            self::Store => 'Loja',
            self::Warehouse => 'Galpão',
            self::Farm => 'Chácara/Sítio',
            self::Other => 'Outro',
        };
    }
}
