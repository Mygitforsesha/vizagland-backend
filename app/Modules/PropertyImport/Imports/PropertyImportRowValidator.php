<?php

namespace App\Modules\PropertyImport\Imports;

use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PropertyImportRowValidator
{
    public function __construct(
        private readonly PropertyFieldConfigurationService $propertyFieldConfigurationService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function validate(array $attributes): array
    {
        $rules = $this->rules();

        $validator = Validator::make($attributes, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->propertyFieldConfigurationService->filterInactiveFieldAttributes(
            $validator->validated(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $rules = [
            'property_approval_authority' => ['nullable', 'string', 'max:255'],
            'property_village' => ['nullable', 'string', 'max:255'],
            'property_nearby_location' => ['nullable', 'string', 'max:255'],
            'property_custom_nearby_location' => ['nullable', 'string', 'max:255'],
            'property_district' => ['nullable', 'string', 'max:255'],
            'property_mandal' => ['nullable', 'string', 'max:255'],
            'property_panchayati' => ['nullable', 'string', 'max:255'],
            'property_gvmc' => ['nullable', 'string', 'max:255'],
            'property_vmrda' => ['nullable', 'string', 'max:255'],
            'property_registration_area' => ['nullable', 'string', 'max:255'],
            'property_authority' => ['nullable', 'string', 'max:255'],
            'property_residential_type' => ['nullable', 'string', 'max:255'],
            'property_commercial_type' => ['nullable', 'string', 'max:255'],
            'property_development_type' => ['nullable', 'string', 'max:255'],
            'property_layout_type' => ['nullable', 'string', 'max:255'],
            'property_construction_status' => ['nullable', 'string', 'max:255'],
            'property_construction_type' => ['nullable', 'string', 'max:255'],
            'property_price' => ['nullable', 'numeric', 'min:0'],
            'property_price_range' => ['nullable', 'string', 'max:255'],
            'property_area' => ['nullable', 'numeric', 'min:0'],
            'property_area_unit' => ['nullable', 'string', 'max:50'],
            'property_price_per_sqft' => ['nullable', 'string', 'max:255'],
            'property_age' => ['nullable', 'string', 'max:255'],
            'property_facing' => ['nullable', 'string', 'max:255'],
            'property_total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'property_floor_number' => ['nullable', 'string', 'max:50'],
            'property_furnishing' => ['nullable', 'string', 'max:255'],
            'property_under' => ['nullable', 'string', 'max:255'],
            'property_lp_no' => ['nullable', 'string', 'max:100'],
            'property_plot_no' => ['nullable', 'string', 'max:100'],
            'property_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'property_bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_owner_name' => ['nullable', 'string', 'max:255'],
            'property_owner_phone' => ['nullable', 'string', 'max:20'],
            'property_owner_email' => ['nullable', 'email', 'max:255'],
            'property_other_service_name' => ['nullable', 'string', 'max:255'],
        ];

        return $this->applyFlatFieldConfiguration($rules);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function applyFlatFieldConfiguration(array $rules): array
    {
        foreach ($this->propertyFieldConfigurationService->listAll() as $configuration) {
            $fieldKey = $configuration->property_field_key;

            if (! array_key_exists($fieldKey, $rules)) {
                continue;
            }

            if (! $configuration->property_field_is_active) {
                $rules[$fieldKey] = ['prohibited'];

                continue;
            }

            if (! $configuration->property_field_is_required) {
                continue;
            }

            $existingRules = (array) $rules[$fieldKey];
            $filteredRules = array_values(array_filter(
                $existingRules,
                static fn (mixed $rule): bool => $rule !== 'nullable',
            ));

            if (! in_array('required', $filteredRules, true)) {
                array_unshift($filteredRules, 'required');
            }

            $rules[$fieldKey] = $filteredRules;
        }

        return $rules;
    }
}
