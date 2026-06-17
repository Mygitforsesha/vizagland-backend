<?php

namespace App\Modules\PropertyImport\Requests;

use App\Modules\PropertyImport\Imports\PropertyImportColumnMapping;
use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyImportRequest extends FormRequest
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
            'property_import_file' => [
                'required',
                'file',
                'mimes:'.implode(',', PropertyImportColumnMapping::allowedExtensions()),
                'max:51200',
            ],
        ];
    }
}
