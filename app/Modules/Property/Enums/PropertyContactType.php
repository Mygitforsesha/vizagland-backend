<?php

namespace App\Modules\Property\Enums;

enum PropertyContactType: string
{
    case Owner = 'owner';
    case Agent = 'agent';
    case Broker = 'broker';
    case Builder = 'builder';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Agent => 'Agent',
            self::Broker => 'Broker',
            self::Builder => 'Builder',
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
