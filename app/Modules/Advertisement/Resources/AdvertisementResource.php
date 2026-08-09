<?php

namespace App\Modules\Advertisement\Resources;

use App\Modules\Advertisement\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Advertisement */
class AdvertisementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'advertisement_id' => $this->advertisement_id,
            'advertisement_title' => $this->advertisement_title,
            'advertisement_description' => $this->advertisement_description,
            'advertisement_type' => $this->advertisement_type->value,
            'advertisement_type_label' => $this->advertisement_type->label(),
            'advertisement_image_path' => $this->advertisement_image_path,
            'advertisement_image_url' => Storage::disk('public')->url($this->advertisement_image_path),
            'advertisement_redirect_url' => $this->advertisement_redirect_url,
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
            'advertisement_created_by_user_id' => $this->advertisement_created_by_user_id,
            'advertisement_created_at' => $this->advertisement_created_at?->toIso8601String(),
            'advertisement_updated_at' => $this->advertisement_updated_at?->toIso8601String(),
        ];
    }
}
