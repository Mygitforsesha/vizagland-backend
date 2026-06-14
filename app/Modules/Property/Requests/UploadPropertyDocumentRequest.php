<?php

namespace App\Modules\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPropertyDocumentRequest extends FormRequest
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
            'property_document' => ['required', 'file', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
