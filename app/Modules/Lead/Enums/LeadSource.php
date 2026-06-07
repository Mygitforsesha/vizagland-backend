<?php

namespace App\Modules\Lead\Enums;

enum LeadSource: string
{
    case Public = 'public';
    case Agent = 'agent';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Agent => 'Agent',
            self::Employee => 'Employee',
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
