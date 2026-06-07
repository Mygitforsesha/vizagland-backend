<?php

namespace App\Modules\Lead\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeadRequest extends FormRequest
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
            'lead_name' => ['required', 'string', 'max:255'],
            'lead_email' => ['nullable', 'email', 'max:255'],
            'lead_phone' => ['required', 'string', 'max:20'],
            'lead_message' => ['nullable', 'string', 'max:5000'],
            'lead_property_id' => ['nullable', 'integer', 'exists:properties,property_id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function leadAttributes(): array
    {
        return $this->only([
            'lead_name',
            'lead_email',
            'lead_phone',
            'lead_message',
            'lead_property_id',
        ]);
    }
}
