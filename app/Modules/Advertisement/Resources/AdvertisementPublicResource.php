<?php

namespace App\Modules\Advertisement\Resources;

use App\Modules\Advertisement\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Advertisement */
class AdvertisementPublicResource extends JsonResource
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

        return [
            'advertisement_id' => $this->advertisement_id,
            'advertisement_title' => $this->advertisement_title,
            'advertisement_description' => $this->advertisement_description,
            'advertisement_type' => $typeVal,
            'advertisement_type_label' => $typeLabel,
            'advertisement_types' => $types ?? [],
            'advertisement_category' => $this->advertisement_category?->value ?? 'general',
            'advertisement_category_label' => $this->advertisement_category?->label() ?? 'General Ads',
            'advertisement_image_url' => Storage::disk('public')->url($this->advertisement_image_path),
            'advertisement_redirect_url' => $this->advertisement_redirect_url,
            'advertisement_display_order' => $this->advertisement_display_order,
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
        ];
    }
}
