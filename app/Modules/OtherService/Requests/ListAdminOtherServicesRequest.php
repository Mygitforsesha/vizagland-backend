<?php

namespace App\Modules\OtherService\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdminOtherServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search') && is_string($this->input('search'))) {
            $search = trim($this->input('search'));
            $this->merge(['search' => $search === '' ? null : $search]);
        }

        if ($this->has('other_service_is_active') && is_string($this->input('other_service_is_active'))) {
            $this->merge([
                'other_service_is_active' => filter_var(
                    $this->input('other_service_is_active'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'other_service_is_active' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', Rule::in([
                'other_service_name',
                'other_service_slug',
                'other_service_sort_order',
                'other_service_is_active',
                'other_service_created_at',
                'other_service_updated_at',
                'other_service_id',
            ])],
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
        return [
            'search' => $this->input('search'),
            'other_service_is_active' => $this->has('other_service_is_active')
                ? $this->boolean('other_service_is_active')
                : null,
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }

    public function sortBy(): string
    {
        return (string) $this->input('sort_by', 'other_service_sort_order');
    }

    public function sortDirection(): string
    {
        return (string) $this->input('sort_direction', 'asc');
    }
}
