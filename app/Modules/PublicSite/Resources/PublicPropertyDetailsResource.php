<?php

namespace App\Modules\PublicSite\Resources;

use App\Modules\Property\Models\Property;
use App\Modules\Property\Resources\PropertyImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PublicPropertyDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_code' => $this->property_code,
            'property_title' => $this->property_title,
            'property_description' => $this->property_description,
            'property_type' => $this->property_type?->value,
            'property_listing_type' => $this->property_listing_type?->value,
            'property_price' => $this->property_price,
            'property_negotiable' => $this->property_negotiable,
            'property_area_sqft' => $this->property_area_sqft,
            'property_area' => $this->property_area,
            'property_area_unit' => $this->property_area_unit?->value,
            'property_bedrooms' => $this->property_bedrooms,
            'property_bathrooms' => $this->property_bathrooms,
            'property_parking' => $this->property_parking,
            'property_address' => $this->property_address,
            'property_locality' => $this->property_locality,
            'property_city' => $this->property_city,
            'property_state' => $this->property_state,
            'property_pincode' => $this->property_pincode,
            'property_ownership_type' => $this->property_ownership_type?->value,
            'property_latitude' => $this->property_latitude,
            'property_longitude' => $this->property_longitude,
            'property_published_at' => $this->property_published_at?->toIso8601String(),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
