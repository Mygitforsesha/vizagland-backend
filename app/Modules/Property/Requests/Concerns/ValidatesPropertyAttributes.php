<?php

namespace App\Modules\Property\Requests\Concerns;

use App\Modules\Property\Enums\PropertyContactType;
use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Enums\PropertyType;
use Illuminate\Validation\Rule;

trait ValidatesPropertyAttributes
{
    /**
     * @return array<string, mixed>
     */
    protected function propertyAttributeRules(): array
    {
        return [
            'property_code' => ['nullable', 'string', 'max:50', 'unique:properties,property_code'],
            'property_title' => ['nullable', 'string', 'max:255'],
            'property_description' => ['nullable', 'string'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'property_listing_type' => ['nullable', Rule::enum(PropertyListingType::class)],
            'property_price' => ['nullable', 'numeric', 'min:0'],
            'property_negotiable' => ['nullable', 'boolean'],
            'property_area_sqft' => ['nullable', 'numeric', 'min:0'],
            'property_bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_parking' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_address' => ['nullable', 'string'],
            'property_locality' => ['nullable', 'string', 'max:255'],
            'property_city' => ['nullable', 'string', 'max:255'],
            'property_state' => ['nullable', 'string', 'max:255'],
            'property_pincode' => ['nullable', 'string', 'max:10'],
            'property_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'property_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'property_contact_name' => ['nullable', 'string', 'max:255'],
            'property_contact_phone' => ['nullable', 'string', 'max:20'],
            'property_contact_type' => ['nullable', Rule::enum(PropertyContactType::class)],
            'property_images' => ['nullable', 'array', 'max:30'],
            'property_images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'property_documents' => ['nullable', 'array', 'max:30'],
            'property_documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:20480'],
        ];
    }
}
