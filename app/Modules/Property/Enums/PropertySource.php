<?php

namespace App\Modules\Property\Enums;

enum PropertySource: string
{
    case Public = 'public';
    case Agent = 'agent';
    case Employee = 'employee';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Agent => 'Agent',
            self::Employee => 'Employee',
            self::Admin => 'Admin',
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
