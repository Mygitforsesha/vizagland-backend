<?php

namespace App\Modules\FollowUp\Requests;

use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\FollowUp\Enums\FollowUpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFollowUpRequest extends FormRequest
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
            'follow_up_type' => ['sometimes', Rule::enum(FollowUpType::class)],
            'follow_up_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'follow_up_scheduled_at' => ['sometimes', 'date'],
            'follow_up_status' => ['sometimes', Rule::enum(FollowUpStatus::class)],
            'follow_up_assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateAttributes(): array
    {
        return $this->only([
            'follow_up_type',
            'follow_up_notes',
            'follow_up_scheduled_at',
            'follow_up_status',
            'follow_up_assigned_to',
        ]);
    }
}
