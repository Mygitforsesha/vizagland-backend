<?php

namespace App\Modules\Lead\Requests;

use App\Modules\Lead\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
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
            'lead_name' => ['sometimes', 'string', 'max:255'],
            'lead_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'lead_phone' => ['sometimes', 'string', 'max:20'],
            'lead_message' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'lead_status' => ['sometimes', Rule::enum(LeadStatus::class)],
            'lead_property_id' => ['sometimes', 'nullable', 'integer', 'exists:properties,property_id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateAttributes(): array
    {
        return $this->only([
            'lead_name',
            'lead_email',
            'lead_phone',
            'lead_message',
            'lead_status',
            'lead_property_id',
        ]);
    }
}
