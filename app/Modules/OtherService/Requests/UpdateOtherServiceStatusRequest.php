<?php

namespace App\Modules\OtherService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOtherServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('other_service_is_active') && is_string($this->input('other_service_is_active'))) {
            $this->merge([
                'other_service_is_active' => filter_var(
                    $this->input('other_service_is_active'),
                    FILTER_VALIDATE_BOOLEAN,
                ),
            ]);
        }

        if ($this->has('is_active') && ! $this->has('other_service_is_active')) {
            $value = $this->input('is_active');

            if (is_string($value)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            $this->merge(['other_service_is_active' => (bool) $value]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'other_service_is_active' => ['required', 'boolean'],
        ];
    }
}
