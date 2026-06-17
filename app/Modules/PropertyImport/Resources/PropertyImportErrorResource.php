<?php

namespace App\Modules\PropertyImport\Resources;

use App\Modules\PropertyImport\Models\PropertyImportError;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PropertyImportError */
class PropertyImportErrorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_import_error_id' => $this->property_import_error_id,
            'property_import_row_number' => $this->property_import_row_number,
            'property_import_error_message' => $this->property_import_error_message,
            'property_import_error_created_at' => $this->property_import_error_created_at?->toIso8601String(),
        ];
    }
}
