<?php

namespace App\Modules\Property\Enums;

enum PropertyStatus: string
{
    case Lead = 'lead';
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case RequestChanges = 'request_changes';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Resolved = 'resolved';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::RequestChanges => 'Request Changes',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Resolved => 'Resolved',
            self::Published => 'Published',
            self::Archived => 'Archived',
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
