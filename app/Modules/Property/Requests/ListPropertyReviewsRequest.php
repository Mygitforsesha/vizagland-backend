<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\ReviewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPropertyReviewsRequest extends FormRequest
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
            'property_id' => ['nullable', 'integer', 'min:1'],
            'property_review_status' => ['nullable', Rule::enum(ReviewStatus::class)],
            'property_review_reviewed_by' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['property_id', 'property_review_status', 'property_review_reviewed_by']);
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 20));
    }
}
