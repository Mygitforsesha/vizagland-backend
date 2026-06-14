<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectAdminPropertyRequest extends FormRequest
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
            'property_rejected_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function rejectedReason(): string
    {
        return (string) $this->input('property_rejected_reason');
    }
}
