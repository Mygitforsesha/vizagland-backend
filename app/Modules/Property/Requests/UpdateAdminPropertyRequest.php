<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertyAreaUnit;
use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Enums\PropertyOwnershipType;
use App\Modules\Property\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Property details
            'property_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_description' => ['sometimes', 'nullable', 'string'],
            'property_project_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_lp_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 5)],
            'property_total_floors' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:200'],
            'property_block_phase' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_flat_door_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_plot_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_floor_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'property_facing' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_area_unit' => ['sometimes', 'nullable', Rule::enum(PropertyAreaUnit::class)],
            'property_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_price_range' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_price_per_sqft' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_age' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_bedrooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'property_bathrooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'property_parking' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'property_furnishing' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_under' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_document_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_document_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 5)],
            'property_registration_office_area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_registration_type' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Group & types
            'property_type' => ['sometimes', 'nullable', Rule::enum(PropertyType::class)],
            'property_listing_type' => ['sometimes', 'nullable', Rule::enum(PropertyListingType::class)],
            'property_ownership_type' => ['sometimes', 'nullable', Rule::enum(PropertyOwnershipType::class)],
            'property_residential_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_commercial_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_development_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_layout_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_construction_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_construction_type' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Location
            'property_address' => ['sometimes', 'nullable', 'string'],
            'property_locality' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_pincode' => ['sometimes', 'nullable', 'string', 'max:10'],
            'property_village' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_nearby_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_custom_nearby_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_mandal' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_panchayati' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_gvmc' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_vmrda' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_registration_area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_authority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_approval_authority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'property_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            // Owner
            'property_owner_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_owner_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'property_owner_email' => ['sometimes', 'nullable', 'email', 'max:255'],

            // Other services
            'property_other_service_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_youtube_video_link' => ['sometimes', 'nullable', 'string', 'max:500'],
            'property_location_link' => ['sometimes', 'nullable', 'string', 'max:500'],

            // Admin metadata (non-workflow)
            'property_is_featured' => ['sometimes', 'boolean'],
            'property_assigned_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,user_id'],
            'property_review_remarks' => ['sometimes', 'nullable', 'string', 'max:2000'],

            // Workflow / system fields — use dedicated action APIs
            'property_status' => ['prohibited'],
            'property_verified' => ['prohibited'],
            'property_record_type' => ['prohibited'],
            'property_parent_property_id' => ['prohibited'],
            'property_reference_id' => ['prohibited'],
            'property_is_deleted' => ['prohibited'],
            'property_view_count' => ['prohibited'],
            'property_lead_count' => ['prohibited'],
            'property_rejected_reason' => ['prohibited'],
            'property_approved_at' => ['prohibited'],
            'property_approved_by_user_id' => ['prohibited'],
            'property_rejected_at' => ['prohibited'],
            'property_rejected_by_user_id' => ['prohibited'],
            'property_archived_at' => ['prohibited'],
            'property_archived_by_user_id' => ['prohibited'],
            'property_restored_at' => ['prohibited'],
            'property_restored_by_user_id' => ['prohibited'],
            'property_resolved_at' => ['prohibited'],
            'property_resolved_by_user_id' => ['prohibited'],
            'property_resolution_remarks' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateAttributes(): array
    {
        $prohibited = [
            'property_status',
            'property_verified',
            'property_record_type',
            'property_parent_property_id',
            'property_reference_id',
            'property_is_deleted',
            'property_view_count',
            'property_lead_count',
            'property_rejected_reason',
            'property_approved_at',
            'property_approved_by_user_id',
            'property_rejected_at',
            'property_rejected_by_user_id',
            'property_archived_at',
            'property_archived_by_user_id',
            'property_restored_at',
            'property_restored_by_user_id',
            'property_resolved_at',
            'property_resolved_by_user_id',
            'property_resolution_remarks',
        ];

        $attributes = [];

        foreach (array_keys($this->rules()) as $field) {
            if (in_array($field, $prohibited, true)) {
                continue;
            }

            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                $attributes[$field] = null;

                continue;
            }

            $attributes[$field] = $value;
        }

        return $attributes;
    }
}
