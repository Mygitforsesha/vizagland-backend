<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewPropertyRequest extends FormRequest
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
            'review_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function remarks(): ?string
    {
        return $this->input('review_remarks');
    }
}
