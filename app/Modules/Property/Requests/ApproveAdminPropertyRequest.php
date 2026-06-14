<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAdminPropertyRequest extends FormRequest
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
            'property_review_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function reviewRemarks(): ?string
    {
        return $this->input('property_review_remarks');
    }
}
