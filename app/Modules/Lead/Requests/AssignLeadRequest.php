<?php

namespace App\Modules\Lead\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignLeadRequest extends FormRequest
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
            'lead_assigned_to' => ['required', 'integer', 'exists:users,id'],
            'lead_assignment_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function assigneeId(): int
    {
        return (int) $this->input('lead_assigned_to');
    }

    public function remarks(): ?string
    {
        return $this->input('lead_assignment_remarks');
    }
}
