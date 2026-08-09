<?php

namespace App\Modules\MasterLocation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdminVillagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['search', 'master_location_district', 'master_location_mandal'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            $normalized[$field] = $trimmed === '' ? null : $trimmed;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'master_location_district' => ['nullable', 'string', 'max:255'],
            'master_location_mandal' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in([
                'master_location_village',
                'master_location_district',
                'master_location_mandal',
                'master_location_created_at',
                'master_location_updated_at',
                'master_location_id',
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
        return $this->only([
            'search',
            'master_location_district',
            'master_location_mandal',
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }

    public function sortBy(): string
    {
        return (string) $this->input('sort_by', 'master_location_village');
    }

    public function sortDirection(): string
    {
        return (string) $this->input('sort_direction', 'asc');
    }
}
