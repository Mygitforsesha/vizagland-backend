<?php

namespace App\Modules\Advertisement\Resources;

use App\Modules\Advertisement\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Advertisement */
class AdvertisementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $typeVal = is_object($this->advertisement_type) && isset($this->advertisement_type->value)
            ? $this->advertisement_type->value
            : (string) ($this->advertisement_type ?? '');

        $typeLabel = is_object($this->advertisement_type) && method_exists($this->advertisement_type, 'label')
            ? $this->advertisement_type->label()
            : $typeVal;

        $types = $this->advertisement_types;
        if (empty($types) && $typeVal !== '') {
            $types = array_filter(array_map('trim', explode(',', $typeVal)));
        }

        $media = AdvertisementMediaFields::forAdvertisement($this->resource);

        return [
            'advertisement_id' => $this->advertisement_id,
            'advertisement_title' => $this->advertisement_title,
            'advertisement_description' => $this->advertisement_description,
            'advertisement_type' => $typeVal,
            'advertisement_type_label' => $typeLabel,
            'advertisement_types' => $types ?? [],
            'advertisement_category' => $this->advertisement_category?->value ?? 'general',
            'advertisement_category_label' => $this->advertisement_category?->label() ?? 'General Ads',
            'advertisement_image_path' => $this->advertisement_image_path,
            'advertisement_image_url' => $media['advertisement_image_url'],
            'advertisement_images' => $media['advertisement_images'],
            'advertisement_documents' => $media['advertisement_documents'],
            'advertisement_youtube_video_links' => $media['advertisement_youtube_video_links'],
            'advertisement_location_links' => $media['advertisement_location_links'],
            'advertisement_redirect_url' => $this->advertisement_redirect_url,
            'advertisement_whatsapp_numbers' => array_values($this->advertisement_whatsapp_numbers ?? []),
            'advertisement_email' => $this->advertisement_email,
            'advertisement_display_order' => $this->advertisement_display_order,
            'advertisement_start_date' => $this->advertisement_start_date?->toDateString(),
            'advertisement_end_date' => $this->advertisement_end_date?->toDateString(),
            'advertisement_is_active' => $this->advertisement_is_active,
            'advertisement_village_id' => $this->advertisement_village_id,
            'village' => $this->village ? [
                'master_location_id' => $this->village->master_location_id,
                'master_location_village' => $this->village->master_location_village,
                'master_location_mandal' => $this->village->master_location_mandal,
                'master_location_district' => $this->village->master_location_district,
            ] : null,
            'property_category' => $this->property_category,
            'property_location' => $this->property_location,
            'property_details' => $this->property_details,
            'advertisement_created_by_user_id' => $this->advertisement_created_by_user_id,
            'advertisement_created_at' => $this->advertisement_created_at?->toIso8601String(),
            'advertisement_updated_at' => $this->advertisement_updated_at?->toIso8601String(),
        ];
    }
}
