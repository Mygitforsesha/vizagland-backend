<?php

namespace App\Modules\Property\Enums;

enum PropertyCreatedByType: string
{
    case Employee = 'employee';
    case Agent = 'agent';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Employee',
            self::Agent => 'Agent',
            self::Public => 'Public',
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
