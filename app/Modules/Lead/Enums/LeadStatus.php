<?php

namespace App\Modules\Lead\Enums;

enum LeadStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Converted = 'converted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Converted => 'Converted',
            self::Closed => 'Closed',
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
