<?php

namespace App\Modules\FollowUp\Enums;

enum FollowUpType: string
{
    case Call = 'call';
    case Visit = 'visit';
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Visit => 'Visit',
            self::Whatsapp => 'WhatsApp',
            self::Email => 'Email',
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
