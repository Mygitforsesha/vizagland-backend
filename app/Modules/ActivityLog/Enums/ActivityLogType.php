<?php

namespace App\Modules\ActivityLog\Enums;

enum ActivityLogType: string
{
    case Authentication = 'authentication';
    case Property = 'property';
    case PropertyReview = 'property_review';
    case User = 'user';
    case Lead = 'lead';
    case FollowUp = 'follow_up';
    case Notification = 'notification';
    case Report = 'report';
    case System = 'system';
    case DuplicateChecker = 'duplicate_checker';

    public function label(): string
    {
        return match ($this) {
            self::Authentication => 'Authentication',
            self::Property => 'Property',
            self::PropertyReview => 'Property Review',
            self::User => 'User',
            self::Lead => 'Lead',
            self::FollowUp => 'Follow Up',
            self::Notification => 'Notification',
            self::Report => 'Report',
            self::System => 'System',
            self::DuplicateChecker => 'Duplicate Checker',
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
