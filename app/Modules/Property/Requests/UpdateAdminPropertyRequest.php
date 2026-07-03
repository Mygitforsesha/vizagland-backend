<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertyAreaUnit;
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
            'property_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_price_range' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_area_unit' => ['sometimes', 'nullable', Rule::enum(PropertyAreaUnit::class)],
            'property_price_per_sqft' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_age' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_facing' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_total_floors' => ['sometimes', 'nullable', 'string', 'max:50'],
            'property_floor_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'property_furnishing' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_under' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_lp_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_plot_no' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 5)],
            'property_bedrooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'property_is_featured' => ['sometimes', 'boolean'],
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
            'property_owner_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_owner_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'property_owner_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'property_other_service_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_review_remarks' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'property_status' => ['prohibited'],
            'property_verified' => ['prohibited'],
            'property_record_type' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateAttributes(): array
    {
        $attributes = [];

        foreach (array_keys($this->rules()) as $field) {
            if (in_array($field, ['property_status', 'property_verified', 'property_record_type'], true)) {
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
