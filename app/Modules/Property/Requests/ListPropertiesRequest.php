<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPropertiesRequest extends FormRequest
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
            'property_status' => ['nullable', Rule::enum(PropertyStatus::class)],
            'property_source' => ['nullable', Rule::enum(PropertySource::class)],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'property_city' => ['nullable', 'string', 'max:255'],
            'property_created_by_type' => ['nullable', Rule::enum(PropertyCreatedByType::class)],
            'property_created_by_id' => ['nullable', 'integer', 'min:1'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only([
            'property_status',
            'property_source',
            'property_type',
            'property_city',
            'property_created_by_type',
            'property_created_by_id',
            'price_min',
            'price_max',
            'created_from',
            'created_to',
        ]);
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 20));
    }

    public function sort(): string
    {
        return $this->input('sort', 'latest');
    }
}
