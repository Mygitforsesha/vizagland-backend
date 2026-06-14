<?php

namespace App\Modules\PropertyFieldConfiguration\Requests;

use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldDataType;
use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePropertyFieldConfigurationRequest extends FormRequest
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
            'property_field_key' => ['required', 'string', 'max:255', 'regex:/^property_[a-z0-9_]+$/', 'unique:property_field_configurations,property_field_key'],
            'property_field_label' => ['required', 'string', 'max:255'],
            'property_field_section' => ['required', Rule::enum(PropertyFieldSection::class)],
            'property_field_data_type' => ['required', Rule::enum(PropertyFieldDataType::class)],
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
        return [
            'property_field_key' => $this->input('property_field_key'),
            'property_field_label' => $this->input('property_field_label'),
            'property_field_section' => $this->input('property_field_section'),
            'property_field_data_type' => $this->input('property_field_data_type'),
            'property_field_is_active' => $this->boolean('property_field_is_active', true),
            'property_field_is_required' => $this->boolean('property_field_is_required', false),
            'property_field_display_order' => (int) $this->input('property_field_display_order', 0),
        ];
    }
}
