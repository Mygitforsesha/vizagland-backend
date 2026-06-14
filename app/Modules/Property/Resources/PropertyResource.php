<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_title' => $this->property_title,
            'property_description' => $this->property_description,
            'property_price' => $this->property_price,
            'property_area' => $this->property_area,
            'property_area_unit' => $this->property_area_unit,
            'property_owner_name' => $this->property_owner_name,
            'property_owner_phone' => $this->property_owner_phone,
            'property_address' => $this->property_address,
            'property_city' => $this->property_city,
            'property_state' => $this->property_state,
            'property_pincode' => $this->property_pincode,
            'property_latitude' => $this->property_latitude,
            'property_longitude' => $this->property_longitude,
            'property_lp_no' => $this->property_lp_no,
            'property_year' => $this->property_year,
            'property_plot_no' => $this->property_plot_no,
            'property_bedroom_count' => $this->property_bedrooms,
            'property_ownership_type' => $this->property_ownership_type?->value,
            'property_status' => $this->property_status->value,
            'property_updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
