<?php

namespace App\Modules\FollowUp\Requests;

use App\Modules\FollowUp\Enums\FollowUpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFollowUpRequest extends FormRequest
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
            'follow_up_type' => ['required', Rule::enum(FollowUpType::class)],
            'follow_up_notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_scheduled_at' => ['required', 'date'],
            'follow_up_property_id' => ['nullable', 'integer', 'exists:properties,property_id', 'required_without:follow_up_lead_id'],
            'follow_up_lead_id' => ['nullable', 'integer', 'exists:leads,lead_id', 'required_without:follow_up_property_id'],
            'follow_up_assigned_to' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function followUpAttributes(): array
    {
        return $this->only([
            'follow_up_type',
            'follow_up_notes',
            'follow_up_scheduled_at',
            'follow_up_property_id',
            'follow_up_lead_id',
            'follow_up_assigned_to',
        ]);
    }
}
