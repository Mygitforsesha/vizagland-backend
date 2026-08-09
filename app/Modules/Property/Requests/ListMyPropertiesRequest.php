<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListMyPropertiesRequest extends FormRequest
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
            'phone_number' => ['required', 'string'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required.',
        ];
    }

    public function phoneNumber(): string
    {
        return (string) $this->input('phone_number');
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
