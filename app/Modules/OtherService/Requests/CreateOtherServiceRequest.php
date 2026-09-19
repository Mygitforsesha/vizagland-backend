<?php

namespace App\Modules\OtherService\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOtherServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = [];

        foreach (['other_service_name', 'other_service_slug', 'other_service_description', 'other_service_icon'] as $field) {
            if (! $this->has($field) || ! is_string($this->input($field))) {
                continue;
            }

            $trimmed = trim($this->input($field));
            $input[$field] = $trimmed === '' ? null : $trimmed;
        }

        if ($this->has('other_service_is_active') && is_string($this->input('other_service_is_active'))) {
            $input['other_service_is_active'] = filter_var(
                $this->input('other_service_is_active'),
                FILTER_VALIDATE_BOOLEAN,
            );
        }

        if ($input !== []) {
            $this->merge($input);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'other_service_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('other_services', 'other_service_name'),
            ],
            'other_service_slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('other_services', 'other_service_slug'),
            ],
            'other_service_description' => ['nullable', 'string', 'max:5000'],
            'other_service_icon' => ['nullable', 'string', 'max:255'],
            'other_service_sort_order' => ['nullable', 'integer', 'min:0'],
            'other_service_is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function otherServiceAttributes(): array
    {
        return [
            'other_service_name' => $this->input('other_service_name'),
            'other_service_slug' => $this->input('other_service_slug'),
            'other_service_description' => $this->input('other_service_description'),
            'other_service_icon' => $this->input('other_service_icon'),
            'other_service_sort_order' => (int) $this->input('other_service_sort_order', 0),
            'other_service_is_active' => $this->boolean('other_service_is_active', true),
        ];
    }
}
