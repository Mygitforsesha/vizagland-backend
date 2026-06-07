<?php

namespace App\Modules\Property\Enums;

enum DuplicateMatchStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Dismissed => 'Dismissed',
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
