<?php

namespace App\Modules\PublicSite\Resources;

use App\Modules\Property\Models\Property;
use App\Modules\Property\Resources\PropertyImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PublicPropertyListItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_title' => $this->property_title,
            'property_type' => $this->property_type?->value,
            'property_listing_type' => $this->property_listing_type?->value,
            'property_price' => $this->property_price,
            'property_city' => $this->property_city,
            'property_locality' => $this->property_locality,
            'property_bedrooms' => $this->property_bedrooms,
            'property_ownership_type' => $this->property_ownership_type?->value,
            'property_published_at' => $this->property_published_at?->toIso8601String(),
            'cover_image' => $this->when(
                $this->relationLoaded('images') && $this->images->isNotEmpty(),
                fn () => new PropertyImageResource($this->images->first()),
            ),
        ];
    }
}
