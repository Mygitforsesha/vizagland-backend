<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class AdminPropertyListItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_parent_property_id' => $this->property_parent_property_id,
            'property_reference_id' => $this->property_reference_id,
            'property_record_type' => $this->property_record_type?->value,
            'property_record_type_label' => $this->property_record_type?->label(),
            'property_status' => $this->property_status->value,
            'property_owner_name' => $this->property_owner_name,
            'property_owner_phone' => $this->property_owner_phone,
            'property_price' => $this->property_price,
            'property_area' => $this->property_area,
            'property_area_unit' => $this->property_area_unit,
            'property_village' => $this->property_village,
            'property_district' => $this->property_district,
            'property_residential_type' => $this->property_residential_type,
            'property_commercial_type' => $this->property_commercial_type,
            'property_created_by_user_id' => $this->property_created_by,
            'property_created_at' => $this->created_at?->toIso8601String(),
            'property_updated_at' => $this->updated_at?->toIso8601String(),
            'property_bedrooms' => $this->property_bedrooms,
            'property_facing' => $this->property_facing,
            'property_furnishing' => $this->property_furnishing,
            'property_view_count' => $this->property_view_count,
            'property_lead_count' => $this->property_lead_count,
            'property_is_featured' => $this->property_is_featured,
            'property_review_remarks' => $this->property_review_remarks,
            'property_approved_at' => $this->property_approved_at?->toIso8601String(),
            'property_cadre' => $this->cadrePayload(),
        ];
    }

    /**
     * @return array{property_posted_by: ?string, property_posted_phone_number: ?string}
     */
    private function cadrePayload(): array
    {
        $poster = $this->resolvePosterUser();

        return [
            'property_posted_by' => $poster?->user_full_name,
            'property_posted_phone_number' => $poster?->user_phone,
        ];
    }

    private function resolvePosterUser(): ?User
    {
        if ($this->relationLoaded('createdBy') && $this->createdBy !== null) {
            return $this->createdBy;
        }

        if (! $this->isVizaglandCopy()) {
            return null;
        }

        $parent = $this->relationLoaded('parentProperty') ? $this->parentProperty : null;

        if ($parent !== null && $parent->relationLoaded('createdBy') && $parent->createdBy !== null) {
            return $parent->createdBy;
        }

        return null;
    }
}
