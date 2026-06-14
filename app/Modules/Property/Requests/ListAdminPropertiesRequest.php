<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdminPropertiesRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'property_record_type' => ['nullable', Rule::enum(PropertyRecordType::class)],
            'property_status' => ['nullable', Rule::enum(PropertyStatus::class)],
            'property_district' => ['nullable', 'string', 'max:255'],
            'property_created_by_user_id' => ['nullable', 'integer', 'min:1'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'sort_by' => ['nullable', Rule::in(['created_at', 'updated_at', 'property_price'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
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
            'search',
            'property_record_type',
            'property_status',
            'property_district',
            'property_created_by_user_id',
            'created_from',
            'created_to',
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }

    public function sortBy(): string
    {
        return $this->input('sort_by', 'created_at');
    }

    public function sortDirection(): string
    {
        return $this->input('sort_direction', 'desc');
    }
}
