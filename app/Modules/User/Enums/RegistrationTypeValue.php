<?php

namespace App\Modules\User\Enums;

enum RegistrationTypeValue: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';
    case OwnerRelative = 'owner_relative';
    case OwnerFriend = 'owner_friend';
    case Realtor = 'realtor';
    case Agent = 'agent';
    case MarketingPerson = 'marketing_person';
    case Promoter = 'promoter';
    case Company = 'company';
    case Builder = 'builder';
    case Developer = 'developer';
    case CivilEngineer = 'civil_engineer';
    case Architect = 'architect';
    case StructuralEngineer = 'structural_engineer';
    case DiamondMember = 'diamond_member';
    case GoldMember = 'gold_member';
    case PlatinumMember = 'platinum_member';
    case BronzeMember = 'bronze_member';
    case Eenadu = 'eenadu';
    case Sakshi = 'sakshi';
    case Vaartha = 'vaartha';
    case AndhraJyothi = 'andhra_jyothi';
    case Hindu = 'hindu';
    case IndianExpress = 'indian_express';
    case Facebook = 'facebook';
    case Twitter = 'twitter';
    case Instagram = 'instagram';
    case Youtube = 'youtube';
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case SocialMedia = 'social_media';
    case Others = 'others';

    public function label(): string
    {
        return match ($this) {
            self::Buyer => 'Buyer',
            self::Seller => 'Seller',
            self::OwnerRelative => 'Owner Relative',
            self::OwnerFriend => 'Owner Friend',
            self::Realtor => 'Realtor',
            self::Agent => 'Agent',
            self::MarketingPerson => 'Marketing Person',
            self::Promoter => 'Promoter',
            self::Company => 'Company',
            self::Builder => 'Builder',
            self::Developer => 'Developer',
            self::CivilEngineer => 'Civil Engineer',
            self::Architect => 'Architect',
            self::StructuralEngineer => 'Structural Engineer',
            self::DiamondMember => 'Diamond Member',
            self::GoldMember => 'Gold Member',
            self::PlatinumMember => 'Platinum Member',
            self::BronzeMember => 'Bronze Member',
            self::Eenadu => 'Eenadu',
            self::Sakshi => 'Sakshi',
            self::Vaartha => 'Vaartha',
            self::AndhraJyothi => 'Andhra Jyothi',
            self::Hindu => 'Hindu',
            self::IndianExpress => 'Indian Express',
            self::Facebook => 'Facebook',
            self::Twitter => 'Twitter',
            self::Instagram => 'Instagram',
            self::Youtube => 'YouTube',
            self::Whatsapp => 'WhatsApp',
            self::Telegram => 'Telegram',
            self::SocialMedia => 'Social Media',
            self::Others => 'Others',
        };
    }

    public function category(): RegistrationTypeCategory
    {
        return match ($this) {
            self::Buyer, self::Seller, self::OwnerRelative, self::OwnerFriend,
            self::Realtor, self::Agent, self::MarketingPerson, self::Promoter,
            self::Company, self::Builder, self::Developer => RegistrationTypeCategory::Role,
            self::CivilEngineer, self::Architect, self::StructuralEngineer => RegistrationTypeCategory::Professional,
            self::DiamondMember, self::GoldMember, self::PlatinumMember, self::BronzeMember => RegistrationTypeCategory::Membership,
            self::Eenadu, self::Sakshi, self::Vaartha, self::AndhraJyothi, self::Hindu, self::IndianExpress => RegistrationTypeCategory::Media,
            self::Facebook, self::Twitter, self::Instagram, self::Youtube,
            self::Whatsapp, self::Telegram, self::SocialMedia => RegistrationTypeCategory::SocialMedia,
            self::Others => RegistrationTypeCategory::Other,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<string>
     */
    public static function valuesForCategory(RegistrationTypeCategory $category): array
    {
        return array_values(array_map(
            fn (self $value) => $value->value,
            array_filter(
                self::cases(),
                fn (self $value) => $value->category() === $category,
            ),
        ));
    }
}
