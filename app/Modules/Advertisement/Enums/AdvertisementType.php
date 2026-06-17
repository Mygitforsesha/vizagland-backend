<?php

namespace App\Modules\Advertisement\Enums;

enum AdvertisementType: string
{
    case Banner = 'banner';
    case HeroBanner = 'hero_banner';
    case SidebarBanner = 'sidebar_banner';
    case PopupBanner = 'popup_banner';
    case FeaturedAd = 'featured_ad';

    public function label(): string
    {
        return match ($this) {
            self::Banner => 'Banner',
            self::HeroBanner => 'Hero Banner',
            self::SidebarBanner => 'Sidebar Banner',
            self::PopupBanner => 'Popup Banner',
            self::FeaturedAd => 'Featured Ad',
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
