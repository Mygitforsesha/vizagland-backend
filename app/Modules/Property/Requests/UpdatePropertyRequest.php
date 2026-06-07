<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertyAreaUnit;
use App\Modules\Property\Enums\PropertyOwnershipType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
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
            'property_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_description' => ['sometimes', 'nullable', 'string'],
            'property_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_area_unit' => ['sometimes', 'nullable', Rule::enum(PropertyAreaUnit::class)],
            'property_owner_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_owner_mobile' => ['sometimes', 'nullable', 'string', 'max:20'],
            'property_address' => ['sometimes', 'nullable', 'string'],
            'property_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_pincode' => ['sometimes', 'nullable', 'string', 'max:10'],
            'property_latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'property_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'property_lp_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 5)],
            'property_plot_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'property_bedroom_count' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'property_ownership_type' => ['sometimes', 'nullable', Rule::enum(PropertyOwnershipType::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateAttributes(): array
    {
        $fieldMap = [
            'property_title' => 'property_title',
            'property_description' => 'property_description',
            'property_price' => 'property_price',
            'property_area' => 'property_area',
            'property_area_unit' => 'property_area_unit',
            'property_owner_name' => 'property_contact_name',
            'property_owner_mobile' => 'property_contact_phone',
            'property_address' => 'property_address',
            'property_city' => 'property_city',
            'property_state' => 'property_state',
            'property_pincode' => 'property_pincode',
            'property_latitude' => 'property_latitude',
            'property_longitude' => 'property_longitude',
            'property_lp_number' => 'property_lp_number',
            'property_year' => 'property_year',
            'property_plot_number' => 'property_plot_number',
            'property_bedroom_count' => 'property_bedrooms',
            'property_ownership_type' => 'property_ownership_type',
        ];

        $attributes = [];

        foreach ($fieldMap as $requestKey => $databaseColumn) {
            if (! $this->has($requestKey)) {
                continue;
            }

            $value = $this->input($requestKey);

            if ($value === null) {
                continue;
            }

            $attributes[$databaseColumn] = $value;
        }

        return $attributes;
    }
}
