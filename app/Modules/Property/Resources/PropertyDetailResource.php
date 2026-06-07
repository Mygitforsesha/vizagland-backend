<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyDetailResource extends JsonResource
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
            'property_type_label' => $this->property_type?->label(),
            'property_listing_type' => $this->property_listing_type?->value,
            'property_listing_type_label' => $this->property_listing_type?->label(),
            'property_price' => $this->property_price,
            'property_negotiable' => $this->property_negotiable,
            'property_area_sqft' => $this->property_area_sqft,
            'property_bedrooms' => $this->property_bedrooms,
            'property_bathrooms' => $this->property_bathrooms,
            'property_parking' => $this->property_parking,
            'property_address' => $this->property_address,
            'property_locality' => $this->property_locality,
            'property_city' => $this->property_city,
            'property_state' => $this->property_state,
            'property_pincode' => $this->property_pincode,
            'property_latitude' => $this->property_latitude,
            'property_longitude' => $this->property_longitude,
            'property_contact_name' => $this->property_contact_name,
            'property_contact_phone' => $this->property_contact_phone,
            'property_contact_type' => $this->property_contact_type?->value,
            'property_contact_type_label' => $this->property_contact_type?->label(),
            'property_source' => $this->property_source?->value,
            'property_source_label' => $this->property_source?->label(),
            'property_status' => $this->property_status->value,
            'property_status_label' => $this->property_status->label(),
            'property_created_by_type' => $this->property_created_by_type?->value,
            'property_created_by_type_label' => $this->property_created_by_type?->label(),
            'property_created_by_id' => $this->property_created_by_id,
            'property_reviewed_by' => $this->property_reviewed_by,
            'property_assigned_to' => $this->property_assigned_to,
            'property_published_at' => $this->property_published_at?->toIso8601String(),
            'property_created_at' => $this->created_at?->toIso8601String(),
            'property_updated_at' => $this->updated_at?->toIso8601String(),
            'creator' => $this->when(
                $this->relationLoaded('createdBy') && $this->createdBy !== null,
                fn () => new PropertyCreatorResource($this->createdBy),
            ),
        ];
    }
}
