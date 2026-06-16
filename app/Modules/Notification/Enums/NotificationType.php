<?php

namespace App\Modules\Notification\Enums;

enum NotificationType: string
{
    case PropertyCreated = 'property_created';
    case PropertySubmitted = 'property_submitted';
    case PropertyApproved = 'property_approved';
    case PropertyRejected = 'property_rejected';
    case PropertyResolved = 'property_resolved';
    case DuplicateDetected = 'duplicate_detected';
    case UserRegistered = 'user_registered';
    case ReportGenerated = 'report_generated';
    case SystemAlert = 'system_alert';

    public function label(): string
    {
        return match ($this) {
            self::PropertyCreated => 'Property Created',
            self::PropertySubmitted => 'Property Submitted',
            self::PropertyApproved => 'Property Approved',
            self::PropertyRejected => 'Property Rejected',
            self::PropertyResolved => 'Property Resolved',
            self::DuplicateDetected => 'Duplicate Detected',
            self::UserRegistered => 'User Registered',
            self::ReportGenerated => 'Report Generated',
            self::SystemAlert => 'System Alert',
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
