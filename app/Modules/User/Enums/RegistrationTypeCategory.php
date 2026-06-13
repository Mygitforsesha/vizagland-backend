<?php

namespace App\Modules\User\Enums;

enum RegistrationTypeCategory: string
{
    case Role = 'role';
    case Professional = 'professional';
    case Membership = 'membership';
    case Media = 'media';
    case SocialMedia = 'social_media';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Role => 'Role',
            self::Professional => 'Professional',
            self::Membership => 'Membership',
            self::Media => 'Media',
            self::SocialMedia => 'Social Media',
            self::Other => 'Other',
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
