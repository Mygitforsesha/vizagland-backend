<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyMyListItemResource extends JsonResource
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
            'property_record_type' => $this->enumValue($this->property_record_type),
            'property_title' => $this->property_title,
            'property_description' => $this->property_description,
            'property_type' => $this->enumValue($this->property_type),
            'property_listing_type' => $this->enumValue($this->property_listing_type),
            'property_price' => $this->property_price,
            'property_negotiable' => $this->property_negotiable,
            'property_area_sqft' => $this->property_area_sqft,
            'property_area' => $this->property_area,
            'property_area_unit' => $this->property_area_unit,
            'property_bedrooms' => $this->property_bedrooms,
            'property_bathrooms' => $this->property_bathrooms,
            'property_parking' => $this->property_parking,
            'property_lp_no' => $this->property_lp_no,
            'property_project_name' => $this->property_project_name,
            'property_block_phase' => $this->property_block_phase,
            'property_document_no' => $this->property_document_no,
            'property_document_year' => $this->property_document_year,
            'property_registration_office_area' => $this->property_registration_office_area,
            'property_year' => $this->property_year,
            'property_plot_no' => $this->property_plot_no,
            'property_ownership_type' => $this->enumValue($this->property_ownership_type),
            'property_address' => $this->property_address,
            'property_locality' => $this->property_locality,
            'property_city' => $this->property_city,
            'property_state' => $this->property_state,
            'property_pincode' => $this->property_pincode,
            'property_latitude' => $this->property_latitude,
            'property_longitude' => $this->property_longitude,
            'property_contact_name' => $this->property_contact_name,
            'property_contact_phone' => $this->property_contact_phone,
            'property_contact_type' => $this->enumValue($this->property_contact_type),
            'property_source' => $this->enumValue($this->property_source),
            'property_status' => $this->enumValue($this->property_status),
            'property_status_label' => $this->property_status?->label(),
            'property_created_by_type' => $this->enumValue($this->property_created_by_type),
            'property_created_by_id' => $this->property_created_by_id,
            'property_created_by' => $this->property_created_by,
            'property_reviewed_by' => $this->property_reviewed_by,
            'property_assigned_user_id' => $this->property_assigned_user_id,
            'property_published_at' => $this->property_published_at?->toIso8601String(),
            'property_other_service_name' => $this->property_other_service_name,
            'property_youtube_video_link' => $this->property_youtube_video_link,
            'property_youtube_video_links' => $this->normalizedUrlLinks(
                $this->property_youtube_video_links,
                $this->property_youtube_video_link,
            ),
            'property_location_link' => $this->property_location_link,
            'property_location_links' => $this->normalizedUrlLinks(
                $this->property_location_links,
                $this->property_location_link,
            ),
            'property_posting_location' => $this->property_posting_location,
            'property_registration_type' => $this->property_registration_type,
            'property_approval_authority' => $this->property_approval_authority,
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
            'property_residential_type' => $this->property_residential_type,
            'property_commercial_type' => $this->property_commercial_type,
            'property_development_type' => $this->property_development_type,
            'property_layout_type' => $this->property_layout_type,
            'property_construction_status' => $this->property_construction_status,
            'property_construction_type' => $this->property_construction_type,
            'property_price_range' => $this->property_price_range,
            'property_price_per_sqft' => $this->property_price_per_sqft,
            'property_age' => $this->property_age,
            'property_facing' => $this->property_facing,
            'property_total_floors' => $this->property_total_floors,
            'property_floor_number' => $this->property_floor_number,
            'property_furnishing' => $this->property_furnishing,
            'property_under' => $this->property_under,
            'property_flat_door_no' => $this->property_flat_door_no,
            'property_owner_name' => $this->property_owner_name,
            'property_owner_phone' => $this->property_owner_phone,
            'property_owner_email' => $this->property_owner_email,
            'property_verified' => $this->property_verified,
            'property_submitted_at' => $this->property_submitted_at?->toIso8601String(),
            'property_is_featured' => $this->property_is_featured,
            'property_view_count' => $this->property_view_count,
            'property_lead_count' => $this->property_lead_count,
            'property_is_deleted' => $this->property_is_deleted,
            'property_review_remarks' => $this->property_review_remarks,
            'property_rejected_reason' => $this->property_rejected_reason,
            'property_approved_at' => $this->property_approved_at?->toIso8601String(),
            'property_approved_by_user_id' => $this->property_approved_by_user_id,
            'property_rejected_at' => $this->property_rejected_at?->toIso8601String(),
            'property_rejected_by_user_id' => $this->property_rejected_by_user_id,
            'property_archived_reason' => $this->property_archived_reason,
            'property_archived_at' => $this->property_archived_at?->toIso8601String(),
            'property_archived_by_user_id' => $this->property_archived_by_user_id,
            'property_restored_at' => $this->property_restored_at?->toIso8601String(),
            'property_restored_by_user_id' => $this->property_restored_by_user_id,
            'property_resolution_remarks' => $this->property_resolution_remarks,
            'property_resolved_at' => $this->property_resolved_at?->toIso8601String(),
            'property_resolved_by_user_id' => $this->property_resolved_by_user_id,
            'property_created_at' => $this->created_at?->toIso8601String(),
            'property_updated_at' => $this->updated_at?->toIso8601String(),
            'images_count' => $this->images_count,
            'documents_count' => $this->documents_count,
            'property_images' => PropertyDetailsImageResource::collection($this->whenLoaded('images')),
            'property_documents' => PropertyDetailsDocumentResource::collection($this->whenLoaded('documents')),
            'property_contact_numbers' => PropertyContactNumberResource::collection($this->whenLoaded('contactNumbers')),
            'created_by_user' => $this->when(
                $this->relationLoaded('createdBy') && $this->createdBy !== null,
                fn () => [
                    'user_id' => $this->createdBy->user_id,
                    'user_full_name' => $this->createdBy->user_full_name,
                    'user_phone' => $this->createdBy->user_phone,
                ],
            ),
        ];
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }

    /**
     * @param  mixed  $links
     * @return list<array{url: string}>
     */
    private function normalizedUrlLinks(mixed $links, mixed $legacySingular = null): array
    {
        $normalized = [];

        if (is_array($links)) {
            foreach ($links as $item) {
                if (is_string($item) && $item !== '') {
                    $normalized[] = ['url' => $item];

                    continue;
                }

                if (! is_array($item)) {
                    continue;
                }

                $url = $item['url'] ?? null;

                if (is_string($url) && $url !== '') {
                    $normalized[] = ['url' => $url];
                }
            }
        }

        if ($normalized === [] && is_string($legacySingular) && $legacySingular !== '') {
            $normalized[] = ['url' => $legacySingular];
        }

        return $normalized;
    }
}
