<?php

namespace App\Modules\User\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Employee = 'employee';
    case Agent = 'agent';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Employee => 'Employee',
            self::Agent => 'Agent',
            self::Member => 'Member',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): self
    {
        return self::from($value);
    }
}
