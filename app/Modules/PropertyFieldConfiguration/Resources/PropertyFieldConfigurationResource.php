<?php

namespace App\Modules\PropertyFieldConfiguration\Resources;

use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldSection;
use App\Modules\PropertyFieldConfiguration\Models\PropertyFieldConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PropertyFieldConfiguration */
class PropertyFieldConfigurationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_field_configuration_id' => $this->property_field_configuration_id,
            'property_field_key' => $this->property_field_key,
            'property_field_label' => $this->property_field_label,
            'property_field_section' => $this->property_field_section,
            'property_field_section_label' => PropertyFieldSection::tryFrom($this->property_field_section)?->label()
                ?? $this->property_field_section,
            'property_field_data_type' => $this->property_field_data_type->value,
            'property_field_data_type_label' => $this->property_field_data_type->label(),
            'property_field_is_active' => $this->property_field_is_active,
            'property_field_is_required' => $this->property_field_is_required,
            'property_field_display_order' => $this->property_field_display_order,
            'property_field_created_at' => $this->property_field_created_at?->toIso8601String(),
            'property_field_updated_at' => $this->property_field_updated_at?->toIso8601String(),
        ];
    }
}
