<?php

namespace App\Modules\Property\Enums;

enum PropertyOwnershipType: string
{
    case Freehold = 'freehold';
    case Leasehold = 'leasehold';
    case CoOperative = 'co_operative';
    case PowerOfAttorney = 'power_of_attorney';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Freehold => 'Freehold',
            self::Leasehold => 'Leasehold',
            self::CoOperative => 'Co-operative',
            self::PowerOfAttorney => 'Power of Attorney',
            self::Other => 'Other',
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
