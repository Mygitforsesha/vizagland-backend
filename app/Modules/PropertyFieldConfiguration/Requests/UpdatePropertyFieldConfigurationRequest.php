<?php

namespace App\Modules\PropertyFieldConfiguration\Requests;

use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldDataType;
use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyFieldConfigurationRequest extends FormRequest
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
        $configurationId = (int) $this->route('id');

        return [
            'property_field_key' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^property_[a-z0-9_]+$/',
                Rule::unique('property_field_configurations', 'property_field_key')->ignore(
                    $configurationId,
                    'property_field_configuration_id',
                ),
            ],
            'property_field_label' => ['sometimes', 'string', 'max:255'],
            'property_field_section' => ['sometimes', Rule::enum(PropertyFieldSection::class)],
            'property_field_data_type' => ['sometimes', Rule::enum(PropertyFieldDataType::class)],
            'property_field_is_active' => ['sometimes', 'boolean'],
            'property_field_is_required' => ['sometimes', 'boolean'],
            'property_field_display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function configurationAttributes(): array
    {
        $attributes = [];

        foreach ([
            'property_field_key',
            'property_field_label',
            'property_field_section',
            'property_field_data_type',
            'property_field_display_order',
        ] as $field) {
            if ($this->has($field)) {
                $attributes[$field] = $this->input($field);
            }
        }

        if ($this->has('property_field_is_active')) {
            $attributes['property_field_is_active'] = $this->boolean('property_field_is_active');
        }

        if ($this->has('property_field_is_required')) {
            $attributes['property_field_is_required'] = $this->boolean('property_field_is_required');
        }

        return $attributes;
    }
}
