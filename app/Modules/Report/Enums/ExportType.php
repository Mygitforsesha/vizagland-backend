<?php

namespace App\Modules\Report\Enums;

enum ExportType: string
{
    case Properties = 'properties';
    case Users = 'users';
    case ActivityLogs = 'activity_logs';
    case Duplicates = 'duplicates';
    case DashboardSummary = 'dashboard_summary';

    public function label(): string
    {
        return match ($this) {
            self::Properties => 'Properties',
            self::Users => 'Users',
            self::ActivityLogs => 'Activity Logs',
            self::Duplicates => 'Duplicates',
            self::DashboardSummary => 'Dashboard Summary',
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
