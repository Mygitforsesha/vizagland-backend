<?php

namespace App\Modules\Property\Enums;

enum PropertySearchSort: string
{
    case Newest = 'newest';
    case PriceAsc = 'price-asc';
    case PriceDesc = 'price-desc';
    case AreaAsc = 'area-asc';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
