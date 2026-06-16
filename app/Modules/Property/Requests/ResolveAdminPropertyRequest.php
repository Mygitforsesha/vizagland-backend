<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveAdminPropertyRequest extends FormRequest
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
            'property_resolution_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function resolutionRemarks(): ?string
    {
        $remarks = $this->input('property_resolution_remarks');

        if (! is_string($remarks)) {
            return null;
        }

        $trimmed = trim($remarks);

        return $trimmed === '' ? null : $trimmed;
    }
}
