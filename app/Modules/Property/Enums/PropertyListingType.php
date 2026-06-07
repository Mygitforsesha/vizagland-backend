<?php

namespace App\Modules\Property\Enums;

enum PropertyListingType: string
{
    case Sale = 'sale';
    case Rent = 'rent';
    case Lease = 'lease';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Sale',
            self::Rent => 'Rent',
            self::Lease => 'Lease',
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
