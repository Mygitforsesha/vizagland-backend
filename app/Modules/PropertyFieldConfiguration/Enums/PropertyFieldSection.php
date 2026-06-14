<?php

namespace App\Modules\PropertyFieldConfiguration\Enums;

enum PropertyFieldSection: string
{
    case PropertyApproval = 'property_approval';
    case PropertyLocation = 'property_location';
    case PropertyGroupAndTypes = 'property_group_and_types';
    case PropertyDetails = 'property_details';
    case PropertyOwner = 'property_owner';
    case PropertyOtherServices = 'property_other_services';
    case PropertyMedia = 'property_media';

    public function label(): string
    {
        return match ($this) {
            self::PropertyApproval => 'Approval',
            self::PropertyLocation => 'Location',
            self::PropertyGroupAndTypes => 'Group & Types',
            self::PropertyDetails => 'Property Details',
            self::PropertyOwner => 'Owner',
            self::PropertyOtherServices => 'Other Services',
            self::PropertyMedia => 'Media',
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
