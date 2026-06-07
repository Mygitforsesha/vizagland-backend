<?php

namespace App\Modules\FollowUp\Enums;

enum FollowUpType: string
{
    case Call = 'call';
    case Email = 'email';
    case Visit = 'visit';
    case Meeting = 'meeting';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Visit => 'Site Visit',
            self::Meeting => 'Meeting',
            self::Note => 'Note',
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
