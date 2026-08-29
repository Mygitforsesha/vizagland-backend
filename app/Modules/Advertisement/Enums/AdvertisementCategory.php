<?php

namespace App\Modules\Advertisement\Enums;

enum AdvertisementCategory: string
{
    case VillageWise = 'village_wise';
    case General = 'general';
    case Latest = 'latest';

    public function label(): string
    {
        return match ($this) {
            self::VillageWise => 'Village Wise Ads',
            self::General => 'General Ads',
            self::Latest => 'Latest Ads',
        };
    }

    /**
     * Normalize flexible input strings into canonical AdvertisementCategory enum.
     */
    public static function fromValue(?string $value): self
    {
        if (empty($value)) {
            return self::General;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'village_wise', 'village_wise_ads', 'village wise ads', 'village_wise_ad', 'village-wise', 'village' => self::VillageWise,
            'general', 'general_ads', 'general ads', 'general_ad' => self::General,
            'latest', 'latest_ads', 'latest ads', 'latest_ad' => self::Latest,
            default => self::tryFrom($value) ?? self::General,
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
