<?php

namespace App\Modules\ActivityLog\Requests;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListActivityLogsRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'activity_log_type' => ['nullable', Rule::enum(ActivityLogType::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,user_id'],
            'activity_log_village' => ['nullable', 'string', 'max:255'],
            'activity_log_district' => ['nullable', 'string', 'max:255'],
            'activity_log_pincode' => ['nullable', 'string', 'max:20'],
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
            'search',
            'activity_log_type',
            'date_from',
            'date_to',
            'user_id',
            'activity_log_village',
            'activity_log_district',
            'activity_log_pincode',
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }
}
