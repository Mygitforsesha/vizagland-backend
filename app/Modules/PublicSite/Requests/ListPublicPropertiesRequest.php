<?php

namespace App\Modules\PublicSite\Requests;

use App\Modules\Property\Enums\PropertyOwnershipType;
use App\Modules\Property\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPublicPropertiesRequest extends FormRequest
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
            'city' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'ownership_type' => ['nullable', Rule::enum(PropertyOwnershipType::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'property_city' => $this->input('city'),
            'property_type' => $this->input('property_type'),
            'price_min' => $this->input('price_min'),
            'price_max' => $this->input('price_max'),
            'bedrooms' => $this->input('bedrooms'),
            'property_ownership_type' => $this->input('ownership_type'),
        ];
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 20));
    }
}
