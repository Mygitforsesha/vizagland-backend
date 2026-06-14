<?php

namespace App\Modules\Report\Enums;

enum ExportFormat: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';

    public function label(): string
    {
        return match ($this) {
            self::Csv => 'CSV',
            self::Xlsx => 'XLSX',
            self::Pdf => 'PDF',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
