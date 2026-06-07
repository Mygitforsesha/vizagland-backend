<?php

namespace App\Modules\Property\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case Villa = 'villa';
    case IndependentHouse = 'independent_house';
    case Plot = 'plot';
    case Commercial = 'commercial';
    case Warehouse = 'warehouse';
    case FarmLand = 'farm_land';

    public function label(): string
    {
        return match ($this) {
            self::Apartment => 'Apartment',
            self::Villa => 'Villa',
            self::IndependentHouse => 'Independent House',
            self::Plot => 'Plot',
            self::Commercial => 'Commercial',
            self::Warehouse => 'Warehouse',
            self::FarmLand => 'Farm Land',
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
