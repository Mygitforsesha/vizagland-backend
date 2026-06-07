<?php

namespace App\Modules\Property\Enums;

enum PropertyAreaUnit: string
{
    case Sqft = 'sqft';
    case Sqm = 'sqm';
    case Acres = 'acres';
    case Hectares = 'hectares';

    public function label(): string
    {
        return match ($this) {
            self::Sqft => 'Sq. Ft.',
            self::Sqm => 'Sq. M.',
            self::Acres => 'Acres',
            self::Hectares => 'Hectares',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
