<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyDetailsResource extends JsonResource
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
            ...$this->linkedRecordPayload(),
            'property_approval' => [
                'property_approval_authority' => $this->property_approval_authority,
                'property_status' => $this->property_status->value,
                'property_status_label' => $this->property_status->label(),
                'property_review_remarks' => $this->property_review_remarks,
                'property_rejected_reason' => $this->property_rejected_reason,
                'property_archived_reason' => $this->property_archived_reason,
                'property_approved_at' => $this->property_approved_at?->toIso8601String(),
                'property_approved_by_user_id' => $this->property_approved_by_user_id,
                'property_rejected_at' => $this->property_rejected_at?->toIso8601String(),
                'property_rejected_by_user_id' => $this->property_rejected_by_user_id,
                'property_archived_at' => $this->property_archived_at?->toIso8601String(),
                'property_archived_by_user_id' => $this->property_archived_by_user_id,
                'property_restored_at' => $this->property_restored_at?->toIso8601String(),
                'property_restored_by_user_id' => $this->property_restored_by_user_id,
            ],
            'property_location' => [
                'property_village' => $this->property_village,
                'property_nearby_location' => $this->property_nearby_location,
                'property_custom_nearby_location' => $this->property_custom_nearby_location,
                'property_district' => $this->property_district,
                'property_mandal' => $this->property_mandal,
                'property_panchayati' => $this->property_panchayati,
                'property_gvmc' => $this->property_gvmc,
                'property_vmrda' => $this->property_vmrda,
                'property_registration_area' => $this->property_registration_area,
                'property_authority' => $this->property_authority,
            ],
            'property_group_and_types' => [
                'property_residential_type' => $this->property_residential_type,
                'property_commercial_type' => $this->property_commercial_type,
                'property_development_type' => $this->property_development_type,
                'property_layout_type' => $this->property_layout_type,
                'property_construction_status' => $this->property_construction_status,
                'property_construction_type' => $this->property_construction_type,
            ],
            'property_details' => [
                'property_price' => $this->property_price,
                'property_price_range' => $this->property_price_range,
                'property_area' => $this->property_area,
                'property_area_unit' => $this->property_area_unit,
                'property_price_per_sqft' => $this->property_price_per_sqft,
                'property_age' => $this->property_age,
                'property_facing' => $this->property_facing,
                'property_total_floors' => $this->property_total_floors,
                'property_floor_number' => $this->property_floor_number,
                'property_furnishing' => $this->property_furnishing,
                'property_under' => $this->property_under,
                'property_lp_no' => $this->property_lp_no,
                'property_plot_no' => $this->property_plot_no,
                'property_year' => $this->property_year,
                'property_bedrooms' => $this->property_bedrooms,
            ],
            'property_owner' => [
                'property_owner_name' => $this->property_owner_name,
                'property_owner_phone' => $this->property_owner_phone,
                'property_owner_email' => $this->property_owner_email,
            ],
            'property_other_services' => [
                'property_service_name' => $this->property_other_service_name,
            ],
            'property_metadata' => [
                'property_is_featured' => $this->property_is_featured,
                'property_view_count' => $this->property_view_count,
                'property_lead_count' => $this->property_lead_count,
                'property_is_deleted' => $this->property_is_deleted,
                'property_assigned_user_id' => $this->property_assigned_user_id,
            ],
            'property_images' => PropertyDetailsImageResource::collection($this->whenLoaded('images')),
            'property_documents' => PropertyDetailsDocumentResource::collection($this->whenLoaded('documents')),
            'created_by_user' => $this->when(
                $this->relationLoaded('createdBy') && $this->createdBy !== null,
                fn () => [
                    'user_id' => $this->createdBy->user_id,
                    'user_full_name' => $this->createdBy->user_full_name,
                    'user_phone' => $this->createdBy->user_phone,
                ],
            ),
            'property_created_at' => $this->created_at?->toIso8601String(),
            'property_updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function linkedRecordPayload(): array
    {
        if ($this->property_record_type === PropertyRecordType::VizaglandCopy) {
            $original = $this->relationLoaded('parentProperty') ? $this->parentProperty : null;

            return [
                'original_property' => $original === null ? null : [
                    'property_id' => $original->property_id,
                    'property_reference_id' => $original->property_reference_id,
                    'property_record_type' => $original->property_record_type?->value,
                ],
            ];
        }

        $vizaglandCopy = $this->relationLoaded('vizaglandCopy') ? $this->vizaglandCopy : null;

        return [
            'vizagland_copy_property' => $vizaglandCopy === null ? null : [
                'property_id' => $vizaglandCopy->property_id,
                'property_reference_id' => $vizaglandCopy->property_reference_id,
                'property_record_type' => $vizaglandCopy->property_record_type?->value,
            ],
        ];
    }
}
