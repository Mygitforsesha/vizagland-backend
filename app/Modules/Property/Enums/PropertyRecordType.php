<?php

namespace App\Modules\Property\Enums;

enum PropertyRecordType: string
{
    case Original = 'original';
    case VizaglandCopy = 'vizagland_copy';

    public function label(): string
    {
        return match ($this) {
            self::Original => 'Original Property',
            self::VizaglandCopy => 'VizagLand Copy',
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
