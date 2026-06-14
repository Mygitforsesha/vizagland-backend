<?php

namespace App\Modules\Property\Enums;

enum PropertyReviewAction: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';
    case Restored = 'restored';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Archived => 'Archived',
            self::Restored => 'Restored',
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
