<?php

namespace App\Modules\PropertyFieldConfiguration\Enums;

enum PropertyFieldDataType: string
{
    case Text = 'text';
    case Number = 'number';
    case Integer = 'integer';
    case Email = 'email';
    case File = 'file';
    case Select = 'select';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Number => 'Number',
            self::Integer => 'Integer',
            self::Email => 'Email',
            self::File => 'File',
            self::Select => 'Select',
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
