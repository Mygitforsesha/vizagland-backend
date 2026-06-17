<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertySearchListItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_reference_id' => $this->property_reference_id,
            'property_title' => $this->property_title,
            'listing_type' => $this->listingTypeLabel(),
            'property_price' => $this->property_price,
            'property_area' => $this->property_area,
            'property_area_unit' => $this->property_area_unit,
            'property_village' => $this->property_village,
            'property_district' => $this->property_district,
            'property_mandal' => $this->property_mandal,
            'property_panchayati' => $this->property_panchayati,
            'property_type' => $this->primaryPropertyType(),
            'property_bedrooms' => $this->property_bedrooms,
            'property_bathrooms' => $this->property_bathrooms,
            'property_parking' => $this->property_parking,
            'property_age' => $this->property_age,
            'property_furnishing' => $this->property_furnishing,
            'property_facing' => $this->property_facing,
            'property_approval_authority' => $this->property_approval_authority,
            'property_published_at' => $this->property_published_at?->toIso8601String(),
            'property_created_at' => $this->created_at?->toIso8601String(),
            'cover_image' => $this->when(
                $this->relationLoaded('images') && $this->images->isNotEmpty(),
                fn () => new PropertyDetailsImageResource($this->images->first()),
            ),
        ];
    }

    private function listingTypeLabel(): ?string
    {
        return match ($this->property_listing_type) {
            PropertyListingType::Sale => 'Buy',
            PropertyListingType::Rent => 'Rent',
            PropertyListingType::Lease => 'Lease',
            default => $this->property_listing_type?->value,
        };
    }

    private function primaryPropertyType(): ?string
    {
        return $this->property_residential_type
            ?? $this->property_commercial_type
            ?? $this->property_development_type
            ?? $this->property_layout_type;
    }
}
