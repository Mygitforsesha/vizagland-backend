<?php

namespace App\Modules\FollowUp\Requests;

use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\FollowUp\Enums\FollowUpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListFollowUpsRequest extends FormRequest
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
            'follow_up_status' => ['nullable', Rule::enum(FollowUpStatus::class)],
            'follow_up_type' => ['nullable', Rule::enum(FollowUpType::class)],
            'follow_up_assigned_to' => ['nullable', 'integer', 'min:1'],
            'follow_up_property_id' => ['nullable', 'integer', 'min:1'],
            'follow_up_lead_id' => ['nullable', 'integer', 'min:1'],
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
            'follow_up_status',
            'follow_up_type',
            'follow_up_assigned_to',
            'follow_up_property_id',
            'follow_up_lead_id',
        ]);
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 20));
    }
}
