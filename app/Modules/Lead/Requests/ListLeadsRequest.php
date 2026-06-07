<?php

namespace App\Modules\Lead\Requests;

use App\Modules\Lead\Enums\LeadSource;
use App\Modules\Lead\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListLeadsRequest extends FormRequest
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
            'lead_status' => ['nullable', Rule::enum(LeadStatus::class)],
            'lead_source' => ['nullable', Rule::enum(LeadSource::class)],
            'lead_assigned_to' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['lead_status', 'lead_source', 'lead_assigned_to']);
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 20));
    }
}
