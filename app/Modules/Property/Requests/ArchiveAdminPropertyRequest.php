<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveAdminPropertyRequest extends FormRequest
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
            'property_archived_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function archivedReason(): ?string
    {
        return $this->input('property_archived_reason');
    }
}
